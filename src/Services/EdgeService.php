<?php declare(strict_types = 1);
namespace EugeneErg\Graph\Services;

use EugeneErg\Graph\Collections\BoolCollection;
use EugeneErg\Graph\Collections\EdgeCollection;
use EugeneErg\Graph\Collections\EdgeCube;
use EugeneErg\Graph\Collections\EdgeMatrix;
use EugeneErg\Graph\Collections\IntegerCollection;
use EugeneErg\Graph\Collections\IntegerMatrix;
use EugeneErg\Graph\Collections\IntersectionCollection;
use EugeneErg\Graph\ValueObjects\AbstractGraph;
use EugeneErg\Graph\ValueObjects\Canvas;
use EugeneErg\Graph\ValueObjects\ClearGraph;
use EugeneErg\Graph\ValueObjects\Intersection;
use EugeneErg\Graph\ValueObjects\Edge;
use EugeneErg\Graph\ValueObjects\Slices\AbstractSlice;
use EugeneErg\Graph\ValueObjects\Slices\ZeroSlice;
use EugeneErg\Graph\ValueObjects\Tree;

class EdgeService extends AbstractService
{
    public function createEdgesFromGraph(AbstractGraph $graph, ?AbstractSlice $slice = null): EdgeCube
    {
        $slice = $slice ?? new ZeroSlice();

        return EdgeCube::fromMap(function (Tree $tree) use ($slice): EdgeMatrix {
            return EdgeMatrix::fromMap(function (ClearGraph $branch) use ($slice): EdgeCollection {
                return $this->toList($this->splitOnTreeEdges($branch, $slice));
            }, false, $tree->branches);
        }, false, TreeService::instance()->createFromGraph($graph));
    }

    public function createConnectedEdgesFromGraph(AbstractGraph $graph, ?AbstractSlice $slice = null): EdgeMatrix
    {
        $slice = $slice ?? new ZeroSlice();

        return EdgeMatrix::fromMap(function (Tree $tree) use ($slice): EdgeCollection {
            return $this->mergeTree(EdgeMatrix::fromWalk(
                $tree->branches,
                function (ClearGraph $branch) use ($slice): EdgeCollection {
                    return $this->toList($this->splitOnTreeEdges($branch, $slice));
                },
                false
            ), $tree, $slice);
        }, false, TreeService::instance()->createFromGraph($graph));
    }

    private function splitOnTreeEdges(ClearGraph $branch, AbstractSlice $slice, ?IntegerCollection $outerEdge = null, int $level = 0): Edge
    {
        if ($branch->vertexes->count() < 4) {
            return new Edge($branch->vertexes);
        }

        $hasOuter = $outerEdge !== null;
        $outerEdge = $outerEdge ?? new IntegerCollection();
        $edgeVertexesKey = $slice->nextKey($branch->vertexes);
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

                    $innerVertexes = $this->getInnerVertexes($branch, $path, $outerVertexes, $slice);

                    if (
                        $first && !$hasOuter
                        && $innerVertexes->count() + $path->count() === $branch->vertexes->count()
                    ) {
                        $innerVertexes = new IntegerCollection();
                    }

                    $first = false;
                    $flipPath = $path->flip();

                    if (!$needOuter || $hasOuter) {
                        if ($innerVertexes->isEmpty()) {
                            $resultChildren[] = new Edge($path);
                        } else {
                            /** @var ClearGraph $graph */
                            $graph = $branch->createSupGraph(
                                IntegerCollection::fromMerge(false, $path, $innerVertexes)
                            );
                            $graph->setOuterEdge($path);
                            $resultChildren[] = $this->splitOnTreeEdges($graph, $slice, $path, $level + 1);
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

    private function mergeTree(EdgeMatrix $edgeMatrix, Tree $tree, AbstractSlice $slice): EdgeCollection
    {
        if ($tree->connections->vertexes->isEmpty()) {
            return $edgeMatrix->getCollection(0);
        }

        $edgeMap = new EdgeCube();
        $lastNumber = 0;
        $addToEdgeList = new EdgeCollection();

        foreach ($edgeMatrix as $branch => $edges) {
            $edgeMap->setMatrix($branch, $this->addEdgeToMap(
                $edges,
                $tree->connections->connections->getCollection($branch),
                $lastNumber
            ));

            if (
                $edges->count() === 1
                && $edges[0]->vertexes->count() > 2
            ) {
                $addToEdgeList[] = $edges[0];
            }

            $lastNumber += $edges->count();
        }

        $edgeLists = EdgeCollection::fromMerge(false, ...$edgeMatrix);

        if (!$addToEdgeList->isEmpty()) {
            $edgeLists->push(...$addToEdgeList);
        }

        $root = $slice->nextKey($edgeMatrix);
        $graph = $tree->connections->direct($root);
        $connections = $graph->getConnections()->getCollection($root) ?? new IntegerCollection();

        while (null !== $keyValue = $connections->getKeyValueByPosition(0)) {
            [$branch, $vertex] = $keyValue;
            unset($connections[$branch]);
            $connections = $connections->replace($graph->getConnections()->getCollection($branch) ?? []);
            $edgeNumberA = $slice->nextKey($edgeMap->getMatrix($root)->getCollection($vertex));
            $edgeA = $edgeMap->getItem($root, $vertex, $edgeNumberA);
            $edgeNumberB = $slice->nextKey($edgeMap->getMatrix($branch)->getCollection($vertex));
            $edgeB = $edgeMap->getItem($branch, $vertex, $edgeNumberB);
            $countAIsW = $edgeA->vertexes->count() === 2;
            $countBIsW = $edgeB->vertexes->count() === 2;

            if ($countAIsW && $countBIsW) {
                $newEdge = $this->getEdgeFromWW($vertex, $edgeA, $edgeB);
                $edgeMap->unsetMatrix($branch);
                $edgeMap->unsetMatrix($root);
                unset($edgeLists[$edgeNumberB]);
                $edgeLists[$edgeNumberA] = $newEdge;
                $edgeLists[] = $newEdge;
                $edgeMap->setMatrix($root, $this->addEdgeToMap(new EdgeCollection([
                    $edgeNumberA => $newEdge,
                ]), $connections));
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

                $edgeMap->setMatrix($root, ($edgeMap->getMatrix($root, true) ?? new EdgeMatrix())->replace(
                    $this->addEdgeToMap(new EdgeCollection([
                        $edgeNumberA => $newEdges[0],
                        $edgeNumberB => $newEdges[1],
                    ]), $connections)
                ));
            }
        }

        return $edgeLists->values();
    }

    private function getPartEdge(Edge $edge, int $offset, int $count = null): IntegerCollection
    {
        $result = new IntegerCollection();
        $vertexCount = $edge->vertexes->count();
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
            $intersect = $vertexes->intersect($subEdge->vertexes);

            foreach ($intersect as $vertex) {
                $result->setItem($vertex, $edgeNumber + $offset, $subEdge);
            }
        }

        return $result;
    }

    private function delEdgeFromMap(EdgeCollection $edgeList, int $branch, EdgeCube $edgeMap): void
    {
        foreach ($edgeList as $edgeNumber => $subEdge) {
            foreach ($subEdge->vertexes as $vertex) {
                $edgeMap->unsetItem($branch, $vertex, $edgeNumber);

                if (
                    $edgeMap->issetCollection($branch, $vertex)
                    && $edgeMap->getCollection($branch, $vertex)->isEmpty()
                ) {
                    $edgeMap->unsetCollection($branch, $vertex);
                }
            }
        }

        if ($edgeMap->getMatrix($branch)->isEmpty()) {
            $edgeMap->unsetMatrix($branch);
        }
    }

    private function getEdgesFromVV(int $vertex, Edge $edgeA, Edge $edgeB): EdgeCollection
    {
        $posA = $edgeA->vertexes->search($vertex, true);
        $posB = $edgeB->vertexes->search($vertex, true);
        $partA = $this->getPartEdge($edgeA, $posA + 1, $edgeA->vertexes->count() >> 1);
        $partB = $this->getPartEdge($edgeB, $posB, ($edgeB->vertexes->count() >> 1) + 1);
        $partB2 = $this->getPartEdge($edgeB, $posB, $partB->count() - $edgeB->vertexes->count() - 2);
        $partA2 = $this->getPartEdge($edgeA, $posA - 1, $partA->count() - $edgeA->vertexes->count());

        for ($i = $partA->count() - 1; $i >= 0; $i--) {
            $partB[] = $partA[$i];
        }

        for ($i = $partA2->count() - 1; $i >= 0; $i--) {
            $partB2[] = $partA2[$i];
        }

        return new EdgeCollection([new Edge($partB), new Edge($partB2)]);
    }

    private function getEdgesFromWV(int $vertex, Edge $edgeA, Edge $edgeB): EdgeCollection
    {
        $posA = $edgeA->vertexes->search($vertex, true);
        $posB = $edgeB->vertexes->search($vertex, true);
        $partA = $this->getPartEdge($edgeA, $posA + 1, $edgeA->vertexes->count() >> 1);
        $partB = $this->getPartEdge($edgeB, $posB, ($edgeB->vertexes->count() >> 1) + 1);
        $partB2 = $this->getPartEdge($edgeB, $posB, $partB->count() - $edgeB->vertexes->count() - 2);

        for ($i = $partA->count() - 1; $i >= 0; $i--) {
            $partB2[] = $partB[] = $partA[$i];
        }

        return new EdgeCollection([new Edge($partB), new Edge($partB2)]);
    }

    private function getEdgeFromWW(int $vertex, Edge $edgeA, Edge $edgeB): Edge
    {
        $posA = $edgeA->vertexes->search($vertex, true);
        $posB = $edgeB->vertexes->search($vertex, true);
        $partA = $this->getPartEdge($edgeA, $posA + 1, $edgeA->vertexes->count() >> 1);
        $partB = $this->getPartEdge($edgeB, $posB, ($edgeB->vertexes->count() >> 1) + 1);

        for ($i = $partA->count() - 1; $i >= 0; $i--) {
            $partB[] = $partA[$i];
        }

        return new Edge($partB);
    }

    private function moveEdgeInMap(int $branch, int $root, int $edgeException, EdgeCube $edgeMap): void
    {
        foreach ($edgeMap->getMatrix($branch) as $vertex => $edges) {
            foreach ($edges as $edgeNumber => $edge) {
                if ($edgeNumber !== $edgeException) {
                    $edgeMap->setItem($root, $vertex, $edgeNumber, $edge);
                    $edgeMap->unsetItem($branch, $vertex, $edgeNumber);

                    if ($edgeMap->getCollection($branch, $vertex)->isEmpty()) {
                        $edgeMap->unsetCollection($branch, $vertex);
                    }
                }
            }
        }

        if ($edgeMap->getMatrix($branch)->isEmpty()) {
            $edgeMap->unsetMatrix($branch);
        }
    }

    private function getInnerVertexes(
        ClearGraph $branch,
        IntegerCollection $path,
        BoolCollection $outerVertexes,
        AbstractSlice $slice
    ): IntegerCollection {
        $innerIntersections = $this->getInnerIntersections($branch, $path, $outerVertexes, $slice);
        $innerVertexes = IntegerMatrix::fromMap(function (Intersection  $intersection): IntegerCollection {
            return $intersection->vertexes;
        }, false, $innerIntersections);

        return IntegerCollection::fromMerge(false, ...$innerVertexes);
    }

    private function getInnerIntersections(
        ClearGraph $branch,
        IntegerCollection $path,
        BoolCollection $outerVertexes,
        AbstractSlice $slice
    ): IntersectionCollection {
        $intersections = IntersectionService::instance()->getIntersections($branch, $path, $outerVertexes);
        $matrix = $this->getIntersectionMatrix($path, $intersections);
        $knowns = new BoolCollection();
        $unknowns = new IntersectionCollection();
        $result = new IntersectionCollection();

        foreach ($intersections as $number => $intersection) {
            $intersection->isOuter
                ? $knowns[$number] = true
                : $unknowns[$number] = $intersection;
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
                $vertexB = $slice->nextKey($unknowns);
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
}
