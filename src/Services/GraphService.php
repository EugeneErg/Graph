<?php namespace EugeneErg\Graph\Services;

use EugeneErg\Graph\ValueObjects\Canvas;
use EugeneErg\Graph\ValueObjects\ClearGraph;
use EugeneErg\Graph\ValueObjects\Edge;
use EugeneErg\Graph\ValueObjects\Graph;
use EugeneErg\Graph\ValueObjects\Intersection;
use EugeneErg\Graph\ValueObjects\Tree;
use Exception;

class GraphService
{
    /* @var CanvasService */
    private $canvasService;
    /** @var ArticulationVertexesFinderService */
    private $articulationVertexesFinderService;
    /** @var EdgeService */
    private $edgeService;
    /** @var IntersectionService */
    private $intersectionService;
    /** @var ViewerService */
    private $viewerService;
    /** @var TreeService */
    private $treeService;

    public function __construct()
    {
        $this->canvasService = new CanvasService();
        $this->articulationVertexesFinderService = new ArticulationVertexesFinderService();
        $this->edgeService = new EdgeService();
        $this->intersectionService = new IntersectionService();
        $this->viewerService = new ViewerService();
        $this->treeService = new TreeService();
    }

    /**
     * @param ClearGraph $graph
     * @return Edge[]
     * @throws Exception
     */
    public function getEdges(ClearGraph $graph): array
    {
        $trees = $this->convertToTrees($graph);
        $result = [];

        foreach ($trees as $tree) {
            $edges = [];

            /** @var ClearGraph $branch */
            foreach ($tree->branches as $number => $branch) {
                $edges[$number] = $this->splitOnTreeEdges($branch);
            }

            $result[] = $this->edgeService->mergeTree($edges, $tree);
        }

        /*foreach ($trees as $tree) {
            /** @var ClearGraph $branch * /
            foreach ($tree->branches as $number => $branch) {
                $result[] = $this->edgeService->toList($this->splitOnTreeEdges($branch));
            }
        }*/

        //$result = count($result) ? array_merge(...$result) : [];

        return $result;
    }

    /**
     * @param ClearGraph $graph
     * @return Tree[]
     */
    private function convertToTrees(ClearGraph $graph): array
    {
        $result = [];

        foreach ($this->splitGraphOnDisconnected($graph) as $graph) {
            $result[] = $this->treeService->fromConnectionGraph($graph);
        }

        return $result;
    }

    /**
     * @param ClearGraph $graph
     * @return int[]
     */
    public function getArticulationVertex(ClearGraph $graph): array
    {
        $result = [];

        foreach ($this->splitGraphOnDisconnected($graph) as $connectedGraphs) {
            $result[] = $this->articulationVertexesFinderService
                ->getArticulationVertexesInConnectedGraph($connectedGraphs);
        }

        return count($result) ? array_replace(...$result) : [];
    }

    public function splitGraphOnDisconnected(ClearGraph $graph): array
    {
        if (!count($graph->vertexes)) {
            return [];
        }

        $canvas = new Canvas($graph);
        $operations = [];

        foreach ($graph->vertexes as $vertex) {
            if ($canvas->getColor($vertex) === 0) {
                $operations[] = $this->canvasService->fill($canvas, $vertex, 1);
            }
        }

        if (count($operations) === 1) {
            return [$graph];
        }

        $result = [];

        foreach ($operations as $operation) {
            $result[] = $graph->createSupGraph(...$operation);
        }

        return $result;
    }

    private function splitOnTreeEdges(ClearGraph $branch, int ...$outerEdge): Edge
    {
        //$steps = [4,3,1,1];
        static $step = 0;

        if (count($branch->vertexes) < 4) {
            return new Edge($branch->vertexes);
        }

        $hasOuter = count($outerEdge) !== 0;
        $edgeVertexesKey = $steps[$step++] ?? array_rand($branch->vertexes);
        var_dump('edgeVertexesKey', $edgeVertexesKey);
        $edgeVertexes = $hasOuter
            ? array_flip($outerEdge)
            : [$branch->vertexes[$edgeVertexesKey] => 0];
        $outerVertexes = [];

        foreach ($edgeVertexes as $vertex => $v) {
            $outerVertexes[$vertex] = true;
        }

        $resultChildren = [];
        $first = !$hasOuter;
        $needOuter = false;
        $finish = false;

        do {
            foreach ($edgeVertexes as $vertexA => $v) {
                unset($edgeVertexes[$vertexA]);
                foreach ($branch->getRow($vertexA) as $vertexB => $value) {
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
                        if ($branch->getValue($vertexA, $vertex) === 1 && $pos > 1) {
                            array_splice($path, $pos + 1);

                            break;
                        }
                    }

                    //echo $this->viewerService->vertexesToSvg('path', $path);
                    $innerVertexes = $this->getInnerVertexes($branch, $path, $outerVertexes);

                    if ($first && !$hasOuter && count($innerVertexes) + count($path) === count($branch->vertexes)) {
                        $innerVertexes = [];
                    }

                    $first = false;
                    $flipPath = array_flip($path);

                    if (!$needOuter || $hasOuter) {
                        if (count($innerVertexes) === 0) {
                            $resultChildren[] = new Edge($path);
                            //echo '<h3>path is new edge</h3>';;
                        } else {
                            /** @var ClearGraph $graph */
                            $graph = $branch->createSupGraph(...$path, ...$innerVertexes);
                            $graph->setOuterEdge($path);
                            $resultChildren[] = $this->splitOnTreeEdges($graph, ...$path);
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

                    $branch->joinOuterEdge($path);
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

        return new Edge($outerEdge, $resultChildren);
    }

    public function findShortEdge(ClearGraph $graph, int $vertexA, int $vertexB, bool $first = false): array
    {
        if ($first) {
            $graph->unsetValue($vertexB, $vertexA);
        } else {
            foreach ($graph->getRow($vertexA) as $vertex => $value) {
                if ($value === 1) {
                    $graph->unsetValue($vertex, $vertexA);
                }
            }
        }

        $result = $this->findShortPath($graph, $vertexA, $vertexB);

        foreach ($graph->getRow($vertexA) as $vertex => $value) {
            $graph->setValue($vertex, $vertexA, $value);
        }

        return $result;
    }

    /**
     * @param ClearGraph $branch
     * @param array $path
     * @param array $outerVertexes
     * @return int[]
     */
    private function getInnerVertexes(ClearGraph $branch, array $path, array $outerVertexes): array
    {
        $innerIntersections = $this->getInnerIntersections($branch, $path, $outerVertexes);
        $innerVertexes = [];

        foreach ($innerIntersections as $intersection) {
            $innerVertexes[] = $intersection->vertexes;
        }

        return count($innerVertexes) ? array_merge(...$innerVertexes) : [];
    }

    private function findShortPath(Graph $graph, int $vertexA, int $vertexB): ?array
    {
        $steps = [[$vertexB => null]];
        $values = [];
        $canvas = new Canvas($graph);

        for ($step = 0; $step < count($steps); $step++) {
            foreach ($steps[$step] as $currentVertex => $prevVertex) {
                $currentValue = !empty($values[$currentVertex]);
                unset($values[$currentVertex]);

                if ($graph->hasConnection($currentVertex, $vertexA)) {
                    $this->canvasService->pixels($canvas, [$vertexA], 1);
                    $steps[$step + 1][$vertexA] = $currentVertex;

                    break(2);
                }

                foreach ($graph->getRow($currentVertex) as $nextVertex => $value) {
                    if (
                        $canvas->getColor($nextVertex) === 0
                        && (
                            (!$currentValue && $value !== 3)
                            || ($currentValue && $value === 2)
                        )
                    ) {
                        $this->canvasService->pixels($canvas, [$vertexA], 1);
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
        $result[] = $currentVertex;

        for ($step = count($steps) - 1; $step > 0; $step--) {
            $currentVertex = $steps[$step][$currentVertex];
            $result[] = $currentVertex;
        }

        return $result;
    }

    /**
     * @param ClearGraph $branch
     * @param array $path
     * @param array $outerVertexes
     * @return Intersection[]
     */
    private function getInnerIntersections(ClearGraph $branch, array $path, array $outerVertexes): array
    {
        $steps = [];
        static $step = 0;

        $intersections = $this->intersectionService->getIntersections($branch, $path, $outerVertexes);
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
        $result = [];

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
                foreach ($matrix->getRow($vertexA) as $vertexB => $value) {
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

    private function getIntersectionMatrix(array $path, array $intersections): ClearGraph
    {
        $matrix = [];

        foreach ($intersections as $number => $intersectionA) {
            for ($i = $number + 1; $i < count($intersections); $i++) {
                $intersectionB = $intersections[$i];

                if ($this->intersectionService->isConflicted($intersectionA, $intersectionB, $path)) {
                    $matrix[$number][$i] = 1;
                }
            }
        }

        return new ClearGraph($matrix, array_keys($intersections));
    }
}
