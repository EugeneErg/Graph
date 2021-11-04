<?php declare(strict_types = 1);
namespace EugeneErg\Graph\Services;

use EugeneErg\Graph\Collections\BoolCollection;
use EugeneErg\Graph\Collections\EdgeCollection;
use EugeneErg\Graph\Collections\EdgeMatrix;
use EugeneErg\Graph\Collections\GraphCollection;
use EugeneErg\Graph\Collections\IntegerCollection;
use EugeneErg\Graph\Collections\IntegerMatrix;
use EugeneErg\Graph\Collections\IntersectionCollection;
use EugeneErg\Graph\Collections\TreeCollection;
use EugeneErg\Graph\ValueObjects\Canvas;
use EugeneErg\Graph\ValueObjects\ClearGraph;
use EugeneErg\Graph\ValueObjects\Edge;
use EugeneErg\Graph\ValueObjects\Graph;
use EugeneErg\Graph\ValueObjects\Intersection;
use EugeneErg\Graph\ValueObjects\Tree;
use Exception;

class GraphService extends AbstractService
{
    /**
     * @param ClearGraph $graph
     * @return EdgeCollection
     * @throws Exception
     */
    public function getEdges(ClearGraph $graph): EdgeCollection
    {
        $trees = TreeService::instance()->createFromGraph($graph);
        $result = new EdgeMatrix();

        foreach ($trees as $tree) {
            $edges = new EdgeCollection();

            /** @var ClearGraph $branch */
            foreach ($tree->branches as $number => $branch) {
                $edges[$number] = $this->splitOnTreeEdges($branch);
            }

            $result[] = EdgeService::instance()->mergeTree($edges, $tree);
        }

        /*foreach ($trees as $tree) {
            /** @var ClearGraph $branch * /
            foreach ($tree->branches as $number => $branch) {
                $result[] = $this->edgeService->toList($this->splitOnTreeEdges($branch));
            }
        }*/

        return EdgeCollection::fromMerge(...$result);
    }

    public function getArticulationVertex(ClearGraph $graph): IntegerCollection
    {
        $result = new IntegerMatrix();

        /** @var ClearGraph $connectedGraphs */
        foreach ($this->splitGraphOnDisconnected($graph) as $connectedGraphs) {
            $result[] = ArticulationVertexesFinderService::instance()
                ->getArticulationVertexesInConnectedGraph($connectedGraphs);
        }

        return count($result) ? IntegerCollection::fromReplace(...$result) : new IntegerCollection();
    }

    public function splitGraphOnDisconnected(ClearGraph $graph): GraphCollection
    {
        if (!count($graph->vertexes)) {
            return new GraphCollection();
        }

        $canvas = new Canvas($graph);
        $operations = IntegerMatrix::fromForeach(
            $graph->vertexes,
            function (int $vertex) use ($canvas): ?IntegerCollection {
                return $canvas->getColor($vertex) === 0
                    ? CanvasService::instance()->fill($canvas, $vertex, 1)
                    : null;
            },
            1,
            true
        );

        if ($operations->count() === 1) {
            return new GraphCollection([$graph]);
        }

        return GraphCollection::fromMap(function(IntegerCollection $operation) use ($graph): Graph {
            return $graph->createSupGraph($operation);
        }, false, $operations);
    }

    private function splitOnTreeEdges(ClearGraph $branch, ?IntegerCollection $outerEdge = null): Edge
    {
        //$steps = [4,3,1,1];
        static $step = 0;

        if (count($branch->vertexes) < 4) {
            return new Edge($branch->vertexes);
        }

        $hasOuter = $outerEdge !== null;
        $outerEdge = $outerEdge ?? new IntegerCollection();
        $edgeVertexesKey = $steps[$step++] ?? array_rand($branch->vertexes);
        var_dump('edgeVertexesKey', $edgeVertexesKey);
        $edgeVertexes = $hasOuter
            ? IntegerCollection::fromFlip($outerEdge)
            : new IntegerCollection([$branch->vertexes[$edgeVertexesKey] => 0]);
        $outerVertexes = BoolCollection::fromMap(function (): bool {return true;}, $edgeVertexes);
        $resultChildren = [];
        $first = !$hasOuter;
        $needOuter = false;
        $finish = false;

        do {
            foreach ($edgeVertexes as $vertexA => $v) {
                unset($edgeVertexes[$vertexA]);
                foreach ($branch->connections[$vertexA] ?? [] as $vertexB => $value) {
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
                        if (($branch->connections[$vertexA][$vertex] ?? null) === 1 && $pos > 1) {
                            $path->splice($pos + 1);

                            break;
                        }
                    }

                    //echo $this->viewerService->vertexesToSvg('path', $path);
                    $innerVertexes = $this->getInnerVertexes($branch, $path, $outerVertexes);

                    if ($first && !$hasOuter && count($innerVertexes) + count($path) === count($branch->vertexes)) {
                        $innerVertexes = [];
                    }

                    $first = false;
                    $flipPath = IntegerCollection::fromFlip($path);

                    if (!$needOuter || $hasOuter) {
                        if (count($innerVertexes) === 0) {
                            $resultChildren[] = new Edge($path->toArray());
                            //echo '<h3>path is new edge</h3>';;
                        } else {
                            /** @var ClearGraph $graph */
                            $graph = $branch->createSupGraph(IntegerCollection::fromMerge($path, $innerVertexes));
                            $graph->setOuterEdge($path->toArray());
                            $resultChildren[] = $this->splitOnTreeEdges($graph, $path);
                        }
                    } elseif (!count($outerEdge)) {
                        $outerEdge = $path;
                        $hasOuter = true;
                    } else {
                        throw new \Exception('Is not planar graph');
                    }

                    foreach ($path as $vertex) {
                        $outerVertexes[$vertex] = true;
                    }

                    $branch->joinOuterEdge($path->toArray());
                    $branch->deleteConnections($innerVertexes);
                    $edgeVertexes = array_replace($edgeVertexes, $flipPath);

                    continue(3);
                }
            }

            $edgeVertexes = array_flip($branch->vertexes);
            $needOuter = true;
        } while (!$finish);

        if (!count($outerEdge)) {
            throw new \Exception('Is not planar graph');
        }

        return new Edge($outerEdge->toArray(), $resultChildren);
    }

    public function findShortEdge(ClearGraph $graph, int $vertexA, int $vertexB, bool $first = false): IntegerCollection
    {
        if ($first) {
            unset($graph->connections[$vertexB][$vertexA]);
        } else {
            foreach ($graph->connections[$vertexA] ?? [] as $vertex => $value) {
                if ($value === 1) {
                    unset($graph->connections[$vertex][$vertexA]);
                }
            }
        }

        $result = $this->findShortPath($graph, $vertexA, $vertexB);

        foreach ($graph->connections[$vertexA] ?? [] as $vertex => $value) {
            $graph->connections[$vertex][$vertexA] = $value;
        }

        return $result;
    }

    private function getInnerVertexes(
        ClearGraph $branch,
        IntegerCollection $path,
        BoolCollection $outerVertexes
    ): IntegerCollection {
        $innerIntersections = $this->getInnerIntersections($branch, $path, $outerVertexes);
        $innerVertexes = new IntegerMatrix();

        foreach ($innerIntersections as $intersection) {
            $innerVertexes[] = $intersection->vertexes;
        }

        return count($innerVertexes) ? IntegerCollection::fromMerge(...$innerVertexes) : new IntegerCollection();
    }

    private function findShortPath(Graph $graph, int $vertexA, int $vertexB): ?IntegerCollection
    {
        $steps = [[$vertexB => null]];
        $values = [];
        $canvas = new Canvas($graph);

        for ($step = 0; $step < count($steps); $step++) {
            foreach ($steps[$step] as $currentVertex => $prevVertex) {
                $currentValue = !empty($values[$currentVertex]);
                unset($values[$currentVertex]);

                if (isset($graph->connections[$currentVertex][$vertexA])) {
                    CanvasService::instance()->pixels($canvas, new IntegerCollection([$vertexA]), 1);
                    $steps[$step + 1][$vertexA] = $currentVertex;

                    break(2);
                }

                foreach ($graph->connections[$currentVertex] ?? [] as $nextVertex => $value) {
                    if (
                        $canvas->getColor($nextVertex) === 0
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

        if ($canvas->getColor($vertexA) === 0) {
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

        $knowns = [];
        $unknowns = [];
        $result = new IntersectionCollection();

        foreach ($intersections as $number => $intersection) {
            if ($intersection->isOuter) {
                $knowns[$number] = true;
            } else {
                $unknowns[$number] = $intersection;
            }
        }

        while (count($unknowns) || count($knowns)) {
            $newKnowns = [];

            foreach ($knowns as $vertexA => $isOuter) {
                foreach ($matrix->connections[$vertexA] ?? [] as $vertexB => $value) {
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

            if (!count($newKnowns) && count($unknowns)) {
                $rand = rand(0, count($unknowns) - 1);
                var_dump('rand', $rand);
                $unknown = array_slice($unknowns, $rand, 1, true);
                $result[] = reset($unknown);
                $vertexB = key($unknown);
                $unknowns[$vertexB]->isOuter = $knowns[$vertexB] = false;
                unset($unknowns[$vertexB]);
            }
        }

        return $result;
    }

    private function getIntersectionMatrix(IntegerCollection $path, IntersectionCollection $intersections): ClearGraph
    {
        $matrix = [];

        foreach ($intersections as $number => $intersectionA) {
            for ($i = $number + 1; $i < count($intersections); $i++) {
                $intersectionB = $intersections[$i];

                if (IntersectionService::instance()->isConflicted($intersectionA, $intersectionB, $path)) {
                    $matrix[$number][$i] = 1;
                }
            }
        }

        return new ClearGraph($matrix, IntegerCollection::fromKeys($intersections)->toArray());
    }
}
