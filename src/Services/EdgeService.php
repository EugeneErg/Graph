<?php declare(strict_types = 1);
namespace EugeneErg\Graph\Services;

use EugeneErg\Graph\Collections\AbstractCollection;
use EugeneErg\Graph\Collections\BoolCollection;
use EugeneErg\Graph\Collections\Collection;
use EugeneErg\Graph\Collections\EdgeCollection;
use EugeneErg\Graph\Collections\EdgeCube;
use EugeneErg\Graph\Collections\EdgeMatrix;
use EugeneErg\Graph\Collections\IntegerCollection;
use EugeneErg\Graph\Collections\IntegerMatrix;
use EugeneErg\Graph\Collections\IntersectionCollection;
use EugeneErg\Graph\ValueObjects\AbstractGraph;
use EugeneErg\Graph\ValueObjects\Arc;
use EugeneErg\Graph\ValueObjects\Canvas;
use EugeneErg\Graph\ValueObjects\ClearGraph;
use EugeneErg\Graph\ValueObjects\Graph;
use EugeneErg\Graph\ValueObjects\Intersection;
use EugeneErg\Graph\ValueObjects\Solution;
use EugeneErg\Graph\ValueObjects\Edge;
use EugeneErg\Graph\ValueObjects\Gravity;
use EugeneErg\Graph\ValueObjects\GravityInterface;
use EugeneErg\Graph\ValueObjects\GravityVertex;
use EugeneErg\Graph\ValueObjects\Replacement;
use EugeneErg\Graph\ValueObjects\Temp\Combine;
use EugeneErg\Graph\ValueObjects\Temp\Path;
use EugeneErg\Graph\ValueObjects\Temp\Problem;
use EugeneErg\Graph\ValueObjects\Temp\Replace;
use EugeneErg\Graph\ValueObjects\Temp\Step;
use EugeneErg\Graph\ValueObjects\Temp\SubGraph;
use EugeneErg\Graph\ValueObjects\Topology;
use EugeneErg\Graph\ValueObjects\Tree;
use EugeneErg\Graph\ValueObjects\Trouble;

class EdgeService extends AbstractService
{
    public function createEdgesFromGraph(AbstractGraph $graph): EdgeCube
    {
        return EdgeCube::fromMap(function (Tree $tree): EdgeMatrix {
            return EdgeMatrix::fromMap(function (ClearGraph $branch): EdgeCollection {
                return $this->toList($this->splitOnTreeEdges($branch));
            }, false, $tree->branches);
        }, false, TreeService::instance()->createFromGraph($graph));
    }

    private function splitOnTreeEdges(ClearGraph $branch, ?IntegerCollection $outerEdge = null, int $level = 0): Edge
    {
        if ($branch->vertexes->count() < 4) {
            return new Edge($branch->vertexes);
        }

        $hasOuter = $outerEdge !== null;
        $outerEdge = $outerEdge ?? new IntegerCollection();
        $edgeVertexesKey = 0;//$branch->vertexes->getRandomKey();
        $edgeVertexes = $hasOuter
            ? $outerEdge->flip()
            : new IntegerCollection([$branch->vertexes[$edgeVertexesKey] => 0]);
        $outerVertexes = BoolCollection::fromFillKeys(
            $hasOuter ? $outerEdge : new IntegerCollection([$branch->vertexes[$edgeVertexesKey]]),
            true
        );
        $resultChildren = new EdgeCollection();
        $first = !$hasOuter;
        $needOuter = false;
        $finish = false;
        $step = 0;

        do {
            foreach ($edgeVertexes as $vertexA => $v) {
                unset($edgeVertexes[$vertexA]);

                foreach ($branch->getColumn($vertexA, true) ?? [] as $vertexB => $value) {
                    if (
                        ($value !== 1 || $needOuter)
                        && ($value !== 2 || !$needOuter)
                    ) {
                        continue;
                    }

                    $step++;

                    if ($needOuter) {
                        $finish = true;
                    }

                    $path = $this->findShortEdge($branch, $vertexA, $vertexB, $first || $finish);

                    if ($path === null) {
                        throw new \Exception('Graph is not planar');
                    }

                    foreach ($path as $pos => $vertex) {
                        if (($branch->getCell($vertexA, $vertex, true) ?? null) === 1 && $pos > 1) {
                            $path->splice($pos + 1);

                            break;
                        }
                    }

                    //echo $this->viewerService->vertexesToSvg('path', $path);
                    if ($step === 2) {
                        //var_dump($path);die;
                    }
                    $innerVertexes = $this->getInnerVertexes($branch, $path, $outerVertexes);

                    if ($step === 2) {
                        //var_dump('innerVertexes', $innerVertexes);die;
                    }

                    if (
                        $first && !$hasOuter
                        && $innerVertexes->count() + $path->count() === $branch->vertexes->count()
                    ) {
                        $innerVertexes = new IntegerCollection();
                    }

                    $first = false;
                    $flipPath = $path->flip();
                    //var_dump(!$needOuter || $hasOuter, $innerVertexes->count() === 0);die;

                    if (!$needOuter || $hasOuter) {
                        if ($innerVertexes->isEmpty()) {
                            $resultChildren[] = new Edge($path);
                            //var_dump($resultChildren);die;
                        } else {
                            if ($level === 0 && $step === 4) {
                                //var_dump($branch, $path, $innerVertexes);die;
                            }

                            /** @var ClearGraph $graph */
                            $graph = $branch->createSupGraph(
                                IntegerCollection::fromMerge(false, $path, $innerVertexes)
                            );
                            //var_dump($path, $innerVertexes, $branch, $graph);die;

                            $graph->setOuterEdge($path);
                            //var_dump($graph, $path);die;

                            //var_dump($graph, $path);die;

                            $resultChildren[] = $this->splitOnTreeEdges($graph, $path, $level + 1);

                            //if ($step === 2) {
                                //die;
                            //}
                        }
                    } elseif ($outerEdge->isEmpty()) {
                        $outerEdge = $path;
                        $hasOuter = true;
                    } else {
                        throw new \Exception('Is not planar graph');
                    }

                    foreach ($path as $vertex) {
                        $outerVertexes[$vertex] = true;
                    }

                    $branch->joinOuterEdge($path);
                    $branch->deleteConnections($innerVertexes);
                    $edgeVertexes = $edgeVertexes->replace($flipPath);
                    //var_dump($edgeVertexes, $branch);die;
                    //var_dump($path, $innerVertexes, $branch);die;

                    if ($step === 3 && $level === 2) {
                        //var_dump($finish, $needOuter, $edgeVertexes, $branch); die;
                    }

                    continue 3;
                }
            }

            $edgeVertexes = $branch->vertexes->flip();
            $needOuter = true;
        } while (!$finish);

        if ($outerEdge->isEmpty()) {
            throw new \Exception('Is not planar graph');
        }

        return new Edge($outerEdge, $resultChildren);
    }

    public function findShortEdge(ClearGraph $graph, int $vertexA, int $vertexB, bool $first = false): IntegerCollection
    {
        static $q = 0;
        $q++;
        //var_dump($vertexA, $vertexB, $graph);

        if ($first) {
            $graph->unsetCell($vertexB, $vertexA, true);
        } else {
            foreach ($graph->getColumn($vertexA, true) ?? [] as $vertex => $value) {
                if ($value === 1) {
                    $graph->unsetCell($vertex, $vertexA, true);
                }
            }
        }

        $result = $this->findShortPath($graph, $vertexA, $vertexB);

        foreach ($graph->getColumn($vertexA, true) ?? [] as $vertex => $value) {
            $graph->setCell($vertex, $vertexA, $value, true);
        }

        //var_dump($result);

        return $result;
    }

    private function findShortPath(AbstractGraph $graph, int $vertexA, int $vertexB): ?IntegerCollection
    {
        $steps = [[$vertexB => null]];
        $values = new BoolCollection();
        $canvas = new Canvas($graph);

        for ($step = 0; $step < count($steps); $step++) {
            foreach ($steps[$step] as $currentVertex => $prevVertex) {
                $currentValue = !empty($values[$currentVertex]);
                unset($values[$currentVertex]);

                if ($graph->issetCell($currentVertex, $vertexA)) {
                    CanvasService::instance()->pixels($canvas, new IntegerCollection([$vertexA]), 1);
                    $steps[$step + 1][$vertexA] = $currentVertex;

                    break(2);
                }

                foreach ($graph->getColumn($currentVertex, true) ?? [] as $nextVertex => $value) {
                    if (
                        $canvas[$nextVertex] === 0
                        && (
                            (!$currentValue && $value !== 3)
                            || ($currentValue && $value === 2)
                        )
                    ) {
                        CanvasService::instance()->pixels($canvas, new IntegerCollection([$vertexA]), 1);
                        $steps[$step + 1][$nextVertex] = $currentVertex;

                        if ($currentValue) {
                            $values[$currentVertex] = true;
                        }
                    }
                }
            }
        }

        if ($canvas[$vertexA] === 0) {
            return null;
        }

        $currentVertex = $vertexA;
        $result = new IntegerCollection([$currentVertex]);

        for ($step = count($steps) - 1; $step > 0; $step--) {
            $currentVertex = $steps[$step][$currentVertex];
            $result[] = $currentVertex;
        }

        return $result;
    }

    public function createListFromGraph(AbstractGraph $graph): EdgeCollection
    {
        $result = EdgeMatrix::fromMap(function (Tree $tree): EdgeCollection {
            return $this->mergeTree(EdgeCollection::fromMap(function (ClearGraph $branch): Edge {
                return $this->splitOnTreeEdges($branch);
            }, false, $tree->branches), $tree);
        }, false, TreeService::instance()->createFromGraph($graph));

        /*foreach ($trees as $tree) {
            /** @var ClearGraph $branch * /
            foreach ($tree->branches as $number => $branch) {
                $result[] = $this->edgeService->toList($this->splitOnTreeEdges($branch));
            }
        }*/

        return EdgeCollection::fromMerge(...$result);
    }

    public function toList(Edge $edge): EdgeCollection
    {
        $result = clone ($parents = new EdgeCollection([$edge]));

        foreach ($parents->getUpdatingIterator() as $parent) {
            foreach ($parent->children as $child) {
                $child->children->isEmpty() ? $result[] = $child : $parents[] = $child;
            }
        }

        return $result;
    }

    private function mergeTree(EdgeCollection $edges, Tree $tree): EdgeCollection
    {
        $step = 0;
        //$steps = [15,13,13,0,12,11,11,1,1,7,7,8,7,9,7,10];
        $edgeMatrix = new EdgeMatrix();
        $edgeMap = new EdgeCube();
        $lastNumber = 0;
        $addToEdgeList = new EdgeCollection();

        foreach ($edges as $branch => $edge) {
            $edgeMatrix[$branch] = $this->toList($edge);
            $edgeMap[$branch] = $this->addEdgeToMap(
                $edgeMatrix[$branch],
                new IntegerCollection($tree->connections[$branch]),
                $lastNumber
            );

            if (
                $edgeMatrix[$branch]->count() === 1
                && count($edgeMatrix[$branch][0]->vertexes) > 2
            ) {
                $addToEdgeList[] = $edgeMatrix[$branch][0];
            }

            $lastNumber += count($edgeMatrix[$branch]);
        }

        $edgeLists = EdgeCollection::fromMerge(...$edgeMatrix);

        if (count($addToEdgeList)) {
            $edgeLists->push(...$addToEdgeList);
        }

        $root = rand(0, count($edges) - 1);
        var_dump('root', $root);
        $graph = $tree->connections->direct($root);
        /**
         *
         * GRAPH:
         *       1
         *      / \
         *    2    6
         *  / | \  |
         * 3  4  5 7
         *        / \
         *       0   8
         *
         *      1,6
         *      / \
         *    2    7
         *  / | \  |\
         * 3  4  5 0 8
         *
         *    1,6,2
         *   / / | \
         * 3  4  5  7
         *          |\
         *          0 8
         *
         *
         * $edgeMap[У какой ветки][какая вершина][есть в каком ребре] = ребро
         * $edgeLists[] - все существующие ребра
         */
        $connections = $graph[$root] ?? new IntegerCollection();

        while (null !== $keyValue = $connections->getKeyValueByPosition(0)) {
            [$branch, $vertex] = $keyValue;
            unset($connections[$branch]);
            $connections = $connections->replace($graph[$branch] ?? []);
            $edgeNumberA = $steps[$step++] ?? $edgeMap[$root][$vertex]->getRandomKey();
            var_dump('edgeNumberA', $edgeNumberA);
            $edgeA = $edgeMap[$root][$vertex][$edgeNumberA];
            $edgeNumberB = $steps[$step++] ?? $edgeMap[$branch][$vertex]->getRandomKey();
            var_dump('edgeNumberB', $edgeNumberB);
            $edgeB = $edgeMap[$branch][$vertex][$edgeNumberB];
            $countAIsW = $edgeA->vertexes->count() === 2;
            $countBIsW = $edgeB->vertexes->count() === 2;

            if ($countAIsW && $countBIsW) {
                $newEdge = $this->getEdgeFromWW($vertex, $edgeA, $edgeB);
                unset(
                    $edgeMap[$branch],
                    $edgeMap[$root],
                    $edgeLists[$edgeNumberB]
                );
                $edgeLists[$edgeNumberA] = $newEdge;
                $edgeLists[] = $newEdge;
                $edgeMap[$root] = $this->addEdgeToMap(new EdgeCollection([
                    $edgeNumberA => $newEdge,
                ]), $connections);
            } else {
                $newEdges = $countAIsW || $countBIsW
                    ? $this->getEdgesFromWV(
                        $vertex,
                        $countAIsW ? $edgeA : $edgeB,
                        $countAIsW ? $edgeB : $edgeA
                    )
                    : $this->getEdgesFromVV($vertex, $edgeA, $edgeB);
                $this->delEdgeFromMap(new EdgeCollection([
                    $edgeNumberA => $edgeA,
                ]), $root, $edgeMap);
                $this->moveEdgeInMap($branch, $root, $edgeNumberB, $edgeMap);
                $edgeLists[$edgeNumberA] = $newEdges[0];
                $edgeLists[$edgeNumberB] = $newEdges[1];
                $edgeMap[$root] = array_replace($edgeMap[$root] ?? [], $this->addEdgeToMap(new EdgeCollection([
                    $edgeNumberA => $newEdges[0],
                    $edgeNumberB => $newEdges[1],
                ]), $connections));
            }
        }

        return $edgeLists->values();
    }

    private function getPartEdge(Edge $edge, int $offset, int $count = null): IntegerCollection
    {
        $result = new IntegerCollection();
        $vertexCount = count($edge->vertexes);
        $count = $count ?? $vertexCount;

        if ($count < 0) {
            $offset += $vertexCount;

            for ($pos = 0; $pos > $count; $pos--) {
                $result[] = $edge->vertexes[($pos + $offset) % $vertexCount];
            }
        } else {
            for ($pos = 0; $pos < $count; $pos++) {
                $result[] = $edge->vertexes[($pos + $offset) % $vertexCount];
            }
        }

        return $result;
    }

    private function addEdgeToMap(EdgeCollection $edgeList, IntegerCollection $vertexes, int $offset = 0): EdgeMatrix
    {
        $result = new EdgeMatrix();

        foreach ($edgeList as $edgeNumber => $subEdge) {
            $intersect = $vertexes->intersect(new Collection($subEdge->vertexes));

            foreach ($intersect as $vertex) {
                $result[$vertex][$edgeNumber + $offset] = $subEdge;
            }
        }

        return $result;
    }

    private function delEdgeFromMap(EdgeCollection $edgeList, int $branch, EdgeCube $edgeMap): void
    {
        foreach ($edgeList as $edgeNumber => $subEdge) {
            foreach ($subEdge->vertexes as $vertex) {
                unset($edgeMap[$branch][$vertex][$edgeNumber]);

                if (empty($edgeMap[$branch][$vertex])) {
                    unset($edgeMap[$branch][$vertex]);
                }
            }
        }

        if (!count($edgeMap[$branch])) {
            unset($edgeMap[$branch]);
        }
    }

    private function getEdgesFromVV(int $vertex, Edge $edgeA, Edge $edgeB): EdgeCollection
    {
        $posA = array_search($vertex, $edgeA->vertexes, true);
        $posB = array_search($vertex, $edgeB->vertexes, true);
        $partA = $this->getPartEdge($edgeA, $posA + 1, count($edgeA->vertexes) >> 1);
        $partB = $this->getPartEdge($edgeB, $posB, (count($edgeB->vertexes) >> 1) + 1);
        $partB2 = $this->getPartEdge($edgeB, $posB, count($partB) - count($edgeB->vertexes) - 2);
        $partA2 = $this->getPartEdge($edgeA, $posA - 1, count($partA) - count($edgeA->vertexes));

        //var_dump($vertex, $edgeA->vertexes, $edgeB->vertexes, $partB, $partB2, $partA, $partA2);die;

        for ($i = count($partA) - 1; $i >= 0; $i--) {
            $partB[] = $partA[$i];
        }

        for ($i = count($partA2) - 1; $i >= 0; $i--) {
            $partB2[] = $partA2[$i];
        }

        return new EdgeCollection([new Edge($partB), new Edge($partB2)]);
    }

    private function getEdgesFromWV(int $vertex, Edge $edgeA, Edge $edgeB): EdgeCollection
    {
        $posA = array_search($vertex, $edgeA->vertexes, true);
        $posB = array_search($vertex, $edgeB->vertexes, true);
        $partA = $this->getPartEdge($edgeA, $posA + 1, count($edgeA->vertexes) >> 1);
        $partB = $this->getPartEdge($edgeB, $posB, (count($edgeB->vertexes) >> 1) + 1);
        $partB2 = $this->getPartEdge($edgeB, $posB, count($partB) - count($edgeB->vertexes) - 2);

        for ($i = count($partA) - 1; $i >= 0; $i--) {
            $partB2[] = $partB[] = $partA[$i];
        }

        return new EdgeCollection([new Edge($partB), new Edge($partB2)]);
    }

    private function getEdgeFromWW(int $vertex, Edge $edgeA, Edge $edgeB): Edge
    {
        $posA = array_search($vertex, $edgeA->vertexes, true);
        $posB = array_search($vertex, $edgeB->vertexes, true);
        $partA = $this->getPartEdge($edgeA, $posA + 1, count($edgeA->vertexes) >> 1);
        $partB = $this->getPartEdge($edgeB, $posB, (count($edgeB->vertexes) >> 1) + 1);

        for ($i = count($partA) - 1; $i >= 0; $i--) {
            $partB[] = $partA[$i];
        }

        return new Edge($partB);
    }

    private function moveEdgeInMap(int $branch, int $root, int $edgeException, EdgeCube $edgeMap): void
    {
        foreach ($edgeMap[$branch] as $vertex => $edges) {
            foreach ($edges as $edgeNumber => $edge) {
                if ($edgeNumber !== $edgeException) {
                    $edgeMap[$root][$vertex][$edgeNumber] = $edge;
                    unset($edgeMap[$branch][$vertex][$edgeNumber]);

                    if (!isset($edgeMap[$branch][$vertex])) {
                        unset($edgeMap[$branch][$vertex]);
                    }
                }
            }
        }

        if (!isset($edgeMap[$branch])) {
            unset($edgeMap[$branch]);
        }
    }

    private function getAdjacencyMatrix()
    {

    }

    public function getTopology(EdgeCollection $edges): Topology
    {
        $outerEdgeNumber = $edges->getRandomKey();
        var_dump('outerEdgeNumber', $outerEdgeNumber);
        $outerEdge = $edges[$outerEdgeNumber];
        unset($edges[$outerEdgeNumber]);
        $arcs = [];
        /** @var Trouble[] $troubleVertexes */
        $troubleVertexes = [];
        /** @var Trouble[][] $troubles */
        $troubles = [];
        $graphs = [new SubGraph($outerEdge, $edges)];

        while (count($graphs)) {
            $graph = array_shift($graphs);

            do {
                $found = 0;
                $nextEdges = [];

                while (count($graph->edges)) {
                    $edge = array_shift($graph->edges);
                    $replacement = $this->getReplacement($graph->counter, $edge);

                    if ($replacement === null) {
                        $nextEdges[] = $edge;

                        continue;
                    }

                    $found++;
                    $replaced = $graph->counter->getVertexes($replacement->start, $replacement->length);
                    $fromVertex = $replacement->firstVertex;
                    $toVertex = $replacement->lastVertex;
                    $prevCounter = $graph->counter;
                    $graph->counter = $graph->counter->replace(
                        $replacement->vertexes,
                        $replacement->start,
                        $replacement->length
                    );

                    if (count($troubles)) {
                        $selectTroubles = [];

                        foreach ($replaced as $vertex) {
                            if (isset($troubleVertexes[$vertex])) {
                                $selectTroubles[$troubleVertexes[$vertex]->fromVertex] = $troubleVertexes[$vertex];
                            }
                        }

                        /** @var Solution[][] $decisions */
                        $decisions = [];

                        foreach ($selectTroubles as $trouble) {
                            $decisionObject = new Solution($trouble, $fromVertex, $toVertex);
                            $decisions[$decisionObject->type][] = $decisionObject;
                        }

                        if ($this->isCircle($decisions)) {
                            $graph->counter = $prevCounter;
                            $found--;
                            $nextEdges[] = $edge;

                            continue;
                        } elseif (isset($decisions[Solution::TYPE_EMBEDDING])) {
                            $trouble = $decisions[Solution::TYPE_EMBEDDING][0]->trouble;

                            for ($i = 1; $i < count($replaced) - 1; $i++) {
                                unset($troubleVertexes[$replaced[$i]]);
                            }

                            for ($i = 1; $i < count($replacement->vertexes) - 1; $i++) {
                                $troubleVertexes[$replacement->vertexes[$i]] = $trouble;
                            }

                            $trouble->embedded($edge, $replacement);

                            continue;
                        } elseif (isset($decisions[Solution::TYPE_ABSORPTION])) {
                            foreach ($decisions[Solution::TYPE_ABSORPTION] as $decision) {
                                foreach ($decision->trouble->vertexes as $vertex) {
                                    unset($troubleVertexes[$vertex]);
                                }

                                unset($troubles[$decision->trouble->fromVertex][$decision->trouble->toVertex]);

                                if (!count($troubles[$decision->trouble->fromVertex])) {
                                    unset($troubles[$decision->trouble->fromVertex]);
                                }
                            }

                            $graphs = array_merge($graphs, $this->applySolution(
                                $decisions[Solution::TYPE_ABSORPTION],
                                $replacement->vertexes,
                                $arcs,
                                $graph
                            ));

                            continue;
                        }
                    }

                    if ($replacement->length === 2 || isset($troubles[$fromVertex][$toVertex])) {
                        if (isset($troubles[$fromVertex][$toVertex])) {
                            for ($i = 1; $i < count($replaced) - 1; $i++) {
                                unset($troubleVertexes[$replaced[$i]]);
                            }
                        } else {
                            $troubles[$fromVertex][$toVertex] = new Trouble(
                                $replaced,
                                $fromVertex,
                                $toVertex
                            );
                        }

                        $troubles[$fromVertex][$toVertex]->embedded(
                            $edge,
                            $replacement
                        );

                        for ($i = 1; $i < count($replacement->vertexes) - 1; $i++) {
                            $troubleVertexes[$replacement->vertexes[$i]] = $troubles[$fromVertex][$toVertex];
                        }
                    } else {
                        $arcs[] = new Arc(new GravityVertex($replaced[count($replaced) >> 1]), $replacement->vertexes);
                    }
                }

                if ($found === 0) {
                    $decisions = [];

                    foreach ($graph->counter->vertexes as $vertex) {
                        if (
                            isset($troubleVertexes[$vertex])
                            && !isset($decisions[$troubleVertexes[$vertex]->fromVertex])
                        ) {
                            $trouble = $troubleVertexes[$vertex];
                            $decisions[$trouble->fromVertex] = $trouble;
                            unset($troubles[$trouble->fromVertex][$trouble->toVertex]);

                            if (!count($troubles[$trouble->fromVertex])) {
                                unset($troubles[$trouble->fromVertex]);
                            }
                        }
                    }

                    if (count($decisions)) {
                        $mainGravityVertexes = [];

                        foreach ($graph->counter->vertexes as $vertex) {
                            if (isset($troubleVertexes[$vertex])) {
                                unset($troubleVertexes[$vertex]);
                            } else {
                                $mainGravityVertexes[] = $vertex;
                            }
                        }

                        $graphs = array_merge($graphs, $this->solutionTroubles(
                            $decisions,
                            $arcs,
                            $mainGravityVertexes
                        ));
                    } elseif (count($nextEdges) > 2) {
                        throw new \Exception();
                    }
                }

                $graph->edges = array_merge($graph->edges, $nextEdges);
            } while ($found !== 0);
        }

        return new Topology($outerEdge, $arcs);
    }

    private function getReplacement(Edge $edgeA, Edge $edgeB): ?Replacement
    {
        $intersectA = array_intersect($edgeA->vertexes, $edgeB->vertexes);
        $intersectCount = count($intersectA);

        if ($intersectCount < 2) {
            return null;
        }

        $edgeCountA = count($edgeA->vertexes);
        $edgeCountB = count($edgeB->vertexes);

        if (
            $edgeCountA === $intersectCount
            && $edgeCountB === $intersectCount
        ) {
            return null;
        }

        if ($edgeCountA === $intersectCount) {
            $intersectB = array_intersect($edgeB->vertexes, $intersectA);
            $shiftsAndDirection = $this->getShiftsAndDirection($intersectB, $edgeCountB, $edgeA, $edgeB);

            if ($shiftsAndDirection === null) {
                return null;
            }

            list($shiftA, $shiftB, $isRightDirection) = $shiftsAndDirection;

            if (!$isRightDirection) {
                $shiftA++;
                $shiftB = $edgeB->findVertex($edgeA->getVertex($shiftA));

                if ($shiftB === null) {
                    return null;
                }
            }
        } else {
            $shiftsAndDirection = $this->getShiftsAndDirection($intersectA, $edgeCountA, $edgeB, $edgeA);

            if ($shiftsAndDirection === null) {
                return null;
            }

            list($shiftB, $shiftA, $isRightDirection) = $shiftsAndDirection;
        }

        for ($i = 0; $i < $intersectCount; $i++) {
            $vertex = $edgeA->getVertex($shiftA + $i);

            if ($vertex !== $edgeB->getVertex($shiftB + ($isRightDirection ? $i : -$i))) {
                return null;
            }
        }

        return new Replacement(
            $edgeB->getVertexes(
                $shiftB,
                $isRightDirection ? $intersectCount - $edgeCountB - 2 : $edgeCountB - $intersectCount + 2
            ),
            $shiftA,
            $intersectCount
        );
    }

    private function getShift(array $vertexes, int $maxCount): ?int
    {
        /*$shift = reset($vertexes) === 0
            && end($vertexes) !== $maxCount;
        $prevValue = -1;
        $result = reset($vertexes);

        foreach ($vertexes as $value) {
            if ($value !== $prevValue + 1) {
                $result = $value;
                $shift++;
            }
            $prevValue = $value;
        }
        return $shift > 1 ? null : $result;*/
        $shift = isset($vertexes[0]) && !isset($vertexes[$maxCount - 1]);
        $prevKey = 0;
        reset($vertexes);
        $result = key($vertexes);

        foreach ($vertexes as $key => $value) {
            if ($key !== $prevKey) {
                $result = $key;

                if ($shift) {
                    return null;
                }

                $shift = true;
            }

            $prevKey = $key + 1;
        }

        return $result;
    }

    private function getShiftsAndDirection(array $intersect, int $edgeCount, Edge $edgeA, Edge $edgeB): ?array
    {
        $shiftB = $this->getShift($intersect, $edgeCount);

        if ($shiftB === null) {
            return null;
        }

        $shiftA = $edgeA->findVertex($edgeB->vertexes[$shiftB]);

        if ($shiftA === null) {
            return null;
        }

        $isRightDirection = $edgeA->getVertex($shiftA + 1) === $edgeB->getVertex($shiftB + 1);

        return [$shiftA, $shiftB, $isRightDirection];
    }

    private function unique(array $array): array
    {
        $result = [];

        while (count($array)) {
            $item = array_shift($array);

            if (!in_array($item, $result, true)) {
                $result[] = $item;
            }
        }

        return $result;
    }

    /**
     * @param int|array $center
     * @return GravityInterface
     */
    private function centerToGravity($center): GravityInterface
    {
        if (is_int($center)) {
            return new GravityVertex($center);
        }

        $subGravity = [];

        foreach ($center as $subCenter) {
            $subGravity[] = $this->centerToGravity($subCenter);
        }

        return new Gravity(...$subGravity);
    }

    /**
     * @param Solution[] $solutions
     * @param int[] $vertexes
     * @param Arc[] $arcs
     * @param SubGraph $subGraph
     * @return SubGraph[]
     */
    private function applySolution(array $solutions, array $vertexes, array &$arcs, SubGraph $subGraph): array
    {
        $doubleSolution = count($solutions) === 2;
        $mainVertexes = $vertexes;
        $count = 0;
        $mainGravityVertexes = [];
        $graphs = [];
        $newArcs = [&$mainVertexes];

        foreach ($solutions as $solution) {
            $mainGravityVertexes[$solution->trouble->fromVertex] = $solution->trouble->fromVertex;
            $mainGravityVertexes[$solution->trouble->toVertex] = $solution->trouble->toVertex;

            if ($solution->fromPosition !== null && $solution->fromVertex !== $solution->trouble->fromVertex) {
                $tree = $solution->trouble->trees[$solution->fromVertex];
                $leftPart = $tree->findPath($solution->fromVertex, false);
                $innerEdges = $solution->trouble->getInnerEdges($solution->trouble->fromVertex, $solution->fromVertex);
                $subGraph->edges = array_merge($subGraph->edges, $innerEdges);
                $pos = $subGraph->counter->findVertex($solution->trouble->fromVertex);
                $pos2 = $subGraph->counter->findVertex($solution->fromVertex);
                $length = $subGraph->counter->getNormalVertexNumber($pos2 - $pos);
                $subGraph->counter = $subGraph->counter->replace($leftPart, $pos, $length);
                $pos = array_search($solution->fromVertex, $solution->trouble->vertexes, true);
                $newArcs[] = $leftArc = array_slice($solution->trouble->vertexes, $pos);
                $leftPart1 = $leftPart;

                if ($count === 0) {
                    $leftPart1[] = array_shift($mainVertexes);
                    $mainVertexes = [$leftPart1, $mainVertexes];
                } else {
                    $leftPart1[] = array_shift($mainVertexes[0]);
                    $mainVertexes = array_merge([$leftPart1], $mainVertexes);
                }

                $count++;
                $innerEdges = array_diff($solution->trouble->edges, $innerEdges);
                $graphs[] = new SubGraph(
                    new Edge(array_merge($leftPart, $leftArc)),
                    $innerEdges
                );
            } elseif ($solution->toPosition !== null && $solution->toVertex !== $solution->trouble->toVertex) {
                $tree = $solution->trouble->trees[$solution->toVertex];
                $rightPath = $tree->findPath($solution->toVertex, true);
                $innerEdges = $solution->trouble->getInnerEdges($solution->toVertex, $solution->trouble->toVertex);
                $subGraph->edges = array_merge($subGraph->edges, $innerEdges);
                $pos = $subGraph->counter->findVertex($solution->toVertex);
                $pos2 = $subGraph->counter->findVertex($solution->trouble->toVertex);
                $length = $subGraph->counter->getNormalVertexNumber($pos2 - $pos);
                $subGraph->counter = $subGraph->counter->replace($rightPath, $pos + 1, $length);
                $pos = array_search($solution->toVertex, $solution->trouble->vertexes, true);
                $newArcs[] = $rightArc = array_slice($solution->trouble->vertexes, 0, $pos + 1);
                $rightPart1 = $rightPath;

                if ($count === 0) {
                    array_unshift($rightPart1, array_pop($mainVertexes));
                    $mainVertexes = [$mainVertexes, $rightPart1];
                } else {
                    array_unshift($rightPart1, array_pop($mainVertexes[1]));
                    $mainVertexes = array_merge($mainVertexes, [$rightPart1]);
                }

                $count++;
                $innerEdges = array_diff($solution->trouble->edges, $innerEdges);
                $graphs[] = new SubGraph(
                    new Edge(array_merge($rightArc, $rightPath)),
                    $innerEdges
                );
            } else {
                $newArcs[] = $solution->trouble->vertexes;
                $graphs[] = new SubGraph(new Edge($solution->trouble->vertexes), $solution->trouble->edges);
            }
        }

        if (is_array($mainVertexes[0])) {
            $mainGravityVertexes[$mainVertexes[0][0]] = $mainVertexes[0][0];
        } else {
            $mainGravityVertexes[$mainVertexes[0]] = $mainVertexes[0];
        }

        $last = end($mainVertexes);

        if (is_array($last)) {
            $last = end($last);
        }

        $mainGravityVertexes[$last] = $last;

        foreach ($newArcs as $arc) {
            if (count($arc) === 1) {
                var_dump('arc', $arc);die;
            }

            $arcs[] = is_array($arc[0])
                ? new Arc(new GravityVertex(...$mainGravityVertexes), ...$arc)
                : new Arc(new GravityVertex(...$mainGravityVertexes), $arc);
        }

        if ($doubleSolution) {
            //var_dump($arcs);die;
        }

        return $graphs;
    }

    /**
     * @param Trouble[] $troubles
     * @param Arc[] $arcs
     * @param int[] $mainGravityVertexes
     * @return SubGraph[]
     */
    private function solutionTroubles(array $troubles, array &$arcs, array $mainGravityVertexes): array
    {
        $graphs = [];

        foreach ($troubles as $trouble) {
            $arcs[] = new Arc(new GravityVertex(...$mainGravityVertexes), $trouble->vertexes);
            $graphs[] = new SubGraph(new Edge($trouble->vertexes), $trouble->edges);
        }

        return $graphs;
    }

    private function getInnerVertexes(
        ClearGraph $branch,
        IntegerCollection $path,
        BoolCollection $outerVertexes
    ): IntegerCollection {
        static $step = 0;
        $step++;

        $innerIntersections = $this->getInnerIntersections($branch, $path, $outerVertexes);

        if ($step === 2) {
            //var_dump($innerIntersections);die;
        }

        $innerVertexes = IntegerMatrix::fromMap(function (Intersection  $intersection): IntegerCollection {
            return $intersection->vertexes;
        }, false, $innerIntersections);

        return IntegerCollection::fromMerge(false, ...$innerVertexes);
    }

    private function getInnerIntersections(
        ClearGraph $branch,
        IntegerCollection $path,
        BoolCollection $outerVertexes
    ): IntersectionCollection {
        $steps = [];
        static $step = 0;

        $intersections = IntersectionService::instance()->getIntersections($branch, $path, $outerVertexes);
        $matrix = $this->getIntersectionMatrix($path, $intersections);


        /*foreach ($intersections as $number => $intersection) {
            //echo $this->viewerService->vertexesToSvg(
                ($intersection->isOuter ? 'outer ' : '') . 'intersection ' . $number,
                array_keys(array_replace(
                    $intersection->vertexes,
                    $intersection->connections
                )),
                $branch->connections
            );
        }

        //echo $this->viewerService->toSvg('intersection matrix', $matrix);*/

        $knowns = new BoolCollection();
        $unknowns = new IntersectionCollection();
        $result = new IntersectionCollection();

        foreach ($intersections as $number => $intersection) {
            if ($intersection->isOuter) {
                $knowns[$number] = true;
            } else {
                $unknowns[$number] = $intersection;
            }
        }

        while (!$unknowns->isEmpty() || !$knowns->isEmpty()) {
            $newKnowns = new BoolCollection();

            foreach ($knowns as $vertexA => $isOuter) {
                foreach ($matrix->getColumn($vertexA, true) ?? [] as $vertexB => $value) {
                    if (isset($unknowns[$vertexB])) {
                        $unknowns[$vertexB]->isOuter = !$isOuter;
                        $newKnowns[$vertexB] = !$isOuter;

                        if ($isOuter) {
                            $result[] = $unknowns[$vertexB];
                        }

                        unset($unknowns[$vertexB]);
                    } elseif ($intersections[$vertexB]->isOuter === $isOuter) {
                        throw new \Exception('graph is not planar');
                    }
                }
            }

            $knowns = $newKnowns;

            if ($newKnowns->isEmpty() && !$unknowns->isEmpty()) {
                $vertexB = $unknowns->getKeyByPosition(0);//$unknowns->getRandomKey();

                $result[] = $unknowns[$vertexB];

                $knowns[$vertexB] = false;
                $unknowns[$vertexB]->isOuter = false;
                unset($unknowns[$vertexB]);
            }
        }

        return $result;
    }

    private function getIntersectionMatrix(IntegerCollection $path, IntersectionCollection $intersections): ClearGraph
    {
        $matrix = new ClearGraph(new IntegerMatrix(), IntegerCollection::fromKeys($intersections));

        foreach ($intersections as $number => $intersectionA) {
            for ($i = $number + 1; $i < $intersections->count(); $i++) {
                $intersectionB = $intersections[$i];

                if (IntersectionService::instance()->isConflicted($intersectionA, $intersectionB, $path)) {
                    $matrix->setCell($number, $i, 1);
                }
            }
        }

        return $matrix;
    }

    /**
     * @param Solution[][] $decisions
     * @return bool
     */
    private function isCircle(array $decisions): bool
    {
        if (count($decisions) === 0) {
            return false;
        }

        if (isset($decisions[Solution::TYPE_CIRCLE])) {
            return true;
        }

        $from = null;
        $to = null;

        foreach ($decisions[Solution::TYPE_ABSORPTION] ?? [] as $decision) {
            if ($decision->fromPosition !== null) {
                $from = $decision;
            } elseif ($decision->toPosition !== null) {
                $to = $decision;
            }
        }

        return $from !== null && $to !== null && $from->trouble->fromVertex === $to->trouble->toVertex;
    }
}
