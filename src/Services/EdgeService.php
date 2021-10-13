<?php declare(strict_types = 1);
namespace EugeneErg\Graph\Services;

use EugeneErg\Graph\ValueObjects\Arc;
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
    /**
     * @param Edge $edge
     * @return Edge[]
     */
    public function toList(Edge $edge): array
    {
        //$result = $parents = [];
        //count($edge->children) ? $parents[] = $edge: $result[] = $edge;
        $result = $parents = [$edge];

        for ($i = 0; $i < count($parents); $i++) {
            foreach ($parents[$i]->children as $child) {
                count($child->children) ? $parents[] = $child : $result[] = $child;
            }
        }

        return $result;
    }

    /**
     * @param Edge[] $edges
     * @param Tree $tree
     */
    public function mergeTree(array $edges, Tree $tree)
    {
        $step = 0;
        //$steps = [15,13,13,0,12,11,11,1,1,7,7,8,7,9,7,10];
        $edgeLists = [];
        /** @var Edge[][][] $edgeMap */
        $edgeMap = [];
        $lastNumber = 0;
        $addToEdgeList = [];

        foreach ($edges as $branch => $edge) {
            $edgeLists[$branch] = $this->toList($edge);
            $edgeMap[$branch] = $this->addEdgeToMap(
                $edgeLists[$branch],
                $tree->connections->connections[$branch],
                $lastNumber
            );

            if (
                count($edgeLists[$branch]) === 1
                && count($edgeLists[$branch][0]->vertexes) > 2
            ) {
                $addToEdgeList[] = $edgeLists[$branch][0];
            }

            $lastNumber += count($edgeLists[$branch]);
        }

        $edgeLists = array_merge(...$edgeLists);

        if (count($addToEdgeList)) {
            array_push($edgeLists, ...$addToEdgeList);
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
        $connections = $graph->getRow($root);

        while (false !== $vertex = reset($connections)) {
            $branch = key($connections);
            unset($connections[$branch]);
            $connections = array_replace($connections, $graph->getRow($branch));
            $edgeNumberA = $steps[$step++] ?? array_rand($edgeMap[$root][$vertex]);
            var_dump('edgeNumberA', $edgeNumberA);
            $edgeA = $edgeMap[$root][$vertex][$edgeNumberA];
            $edgeNumberB = $steps[$step++] ?? array_rand($edgeMap[$branch][$vertex]);
            var_dump('edgeNumberB', $edgeNumberB);
            $edgeB = $edgeMap[$branch][$vertex][$edgeNumberB];
            $countAIsW = count($edgeA->vertexes) === 2;
            $countBIsW = count($edgeB->vertexes) === 2;

            if ($countAIsW && $countBIsW) {
                $newEdge = $this->getEdgeFromWW($vertex, $edgeA, $edgeB);
                unset(
                    $edgeMap[$branch],
                    $edgeMap[$root],
                    $edgeLists[$edgeNumberB]
                );
                $edgeLists[$edgeNumberA] = $newEdge;
                $edgeLists[] = $newEdge;
                $edgeMap[$root] = $this->addEdgeToMap([
                    $edgeNumberA => $newEdge,
                ], $connections);
            } else {
                $newEdges = $countAIsW || $countBIsW
                    ? $this->getEdgesFromWV(
                        $vertex,
                        $countAIsW ? $edgeA : $edgeB,
                        $countAIsW ? $edgeB : $edgeA
                    )
                    : $this->getEdgesFromVV($vertex, $edgeA, $edgeB);
                $this->delEdgeFromMap([
                    $edgeNumberA => $edgeA,
                ], $root, $edgeMap);
                $this->moveEdgeInMap($branch, $root, $edgeNumberB, $edgeMap);
                $edgeLists[$edgeNumberA] = $newEdges[0];
                $edgeLists[$edgeNumberB] = $newEdges[1];
                $edgeMap[$root] = array_replace($edgeMap[$root] ?? [], $this->addEdgeToMap([
                    $edgeNumberA => $newEdges[0],
                    $edgeNumberB => $newEdges[1],
                ], $connections));
            }
        }

        return array_values($edgeLists);
    }

    private function getPartEdge(Edge $edge, int $offset, int $count = null): ?array
    {
        $result = [];
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

    /**
     * @param Edge[] $edgeList
     * @param array $vertexes
     * @param int $offset
     * @return array
     */
    private function addEdgeToMap(array $edgeList, array $vertexes, int $offset = 0): array
    {
        $result = [];

        foreach ($edgeList as $edgeNumber => $subEdge) {
            $intersect = array_intersect($vertexes, $subEdge->vertexes);

            foreach ($intersect as $vertex) {
                $result[$vertex][$edgeNumber + $offset] = $subEdge;
            }
        }

        return $result;
    }

    /**
     * @param Edge[] $edgeList
     * @param int $branch
     * @param array $edgeMap
     */
    private function delEdgeFromMap(array $edgeList, int $branch, array &$edgeMap)
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

    private function getKeyFromNumber(array $array, int $position): ?int
    {
        $valueWithKey = array_slice($array, $position, 1, true);
        reset($valueWithKey);

        return key($valueWithKey);
    }

    private function getEdgesFromVV(int $vertex, Edge $edgeA, Edge $edgeB): array
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

        return [new Edge($partB), new Edge($partB2)];
    }

    /**
     * @param int $vertex
     * @param Edge $edgeA
     * @param Edge $edgeB
     * @return Edge[]
     */
    private function getEdgesFromWV(int $vertex, Edge $edgeA, Edge $edgeB): array
    {
        $posA = array_search($vertex, $edgeA->vertexes, true);
        $posB = array_search($vertex, $edgeB->vertexes, true);
        $partA = $this->getPartEdge($edgeA, $posA + 1, count($edgeA->vertexes) >> 1);
        $partB = $this->getPartEdge($edgeB, $posB, (count($edgeB->vertexes) >> 1) + 1);
        $partB2 = $this->getPartEdge($edgeB, $posB, count($partB) - count($edgeB->vertexes) - 2);

        for ($i = count($partA) - 1; $i >= 0; $i--) {
            $partB2[] = $partB[] = $partA[$i];
        }

        return [new Edge($partB), new Edge($partB2)];
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

    private function moveEdgeInMap(int $branch, int $root, int $edgeException, array &$edgeMap): void
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

    /**
     * @param Edge[] $edges
     * @return Topology
     */
    public function getTopology(array $edges): Topology
    {
        $outerEdgeNumber = array_rand($edges);
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
