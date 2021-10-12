<?php namespace EugeneErg\Graphs;

class Graph
{
    /** @var Vertex[] */
    private $vertexes;
    /** @var self[]|null */
    private $branches;
    /** @var AdjacencyMatrix */
    private $connections;
    /** @var AdjacencyMatrix */
    private $matrix;
    /** @var Canvas */
    private $canvas;

    /**
     * Graph constructor.
     * @param array[] $connections
     */
    public function  __construct(array $connections)
    {
        $this->connections = new AdjacencyMatrix($connections);
        $this->vertexes = [];
        $this->branches = null;
        $vertexIndex = array_flip(array_keys(array_replace($connections, ...$connections)));
        $matrix = [];

        foreach ($vertexIndex as $caption => $index) {
            $this->vertexes[$index] = new Vertex((string) $caption);
        }

        foreach ($connections as $vertexAName => $values) {
            $indexA = $vertexIndex[$vertexAName];

            foreach ($values as $vertexBName => $value) {
                if ($value === null) {
                    continue;
                }

                $indexB = $vertexIndex[$vertexBName];

                if ($value !== null && $indexA !== $indexB && !isset($matrix[$indexA][$indexB])) {
                    $matrix[$indexA][$indexB] = 1;
                    $matrix[$indexB][$indexA] = 1;
                }
            }
        }

        $this->matrix = new AdjacencyMatrix($matrix);
        $this->canvas = new Canvas($this->matrix);
    }

    /**
     * @return Vertex[]
     */
    public function getVertexes(): array
    {
        return $this->vertexes;
    }

    /**
     * @return self[]|null
     */
    public function getBranches(): ?array
    {
        return $this->branches;
    }

    /**
     * @param int ...$vertexes
     * @return $this
     */
    public function createSupGraph(int ...$vertexes): self
    {
        $result = new self([]);
        $result->vertexes = $this->valuesToVertexes($vertexes);
        $result->connections = $this->connections->createSubMatrix(...$vertexes);
        $result->matrix = $this->matrix->createSubMatrix(...$vertexes);
        $result->canvas = new Canvas($result->matrix);

        return $result;
    }

    /**
     * @return self[]
     */
    public function splitGraphOnDisconnected(): array
    {
        if (!isset($this->vertexes[0])) {
            return [];
        }

        $canvas = $this->canvas;
        $vertex = 0;
        $operations = [];

        while ($canvas->getColorCount(0) !== 0) {
            for (; $vertex < count($this->vertexes); $vertex++) {
                if ($canvas->getColor($vertex) === 0) {
                    $canvas = $canvas->fill($vertex, 1);
                    $operations[] = $canvas->getOperations();
                }
            }
        }

        if (count($operations) === 1) {
            return [$this];
        }

        $result = [];

        foreach ($operations as $operation) {
            $result[] = $this->createSupGraph(...$operation);
        }

        return $result;
    }

    public function convertToTree(): Tree
    {
        $branches = [];
        $connections = [];

        foreach ($this->splitGraphOnDisconnected() as $graph) {
            $connection = $graph->splitByBranches($branches);

            if ($connection !== null) {
                $connections[] = $connection;
            }
        }

        return new Tree($this, $branches, count($connections) ? array_replace(...$connections) : []);
    }

    /**
     * @return Edge[]
     * @throws \Exception
     */
    public function getEdges(): array
    {
        $tree = $this->convertToTree();
        $result = [];

        foreach ($tree->getBranches() as $branch) {
            $result[] = $branch->splitOnTreeEdges()->asList();
        }

        $result = count($result) ? array_merge(...$result) : [];

        /** @var Edge $edge */
        foreach ($result as $edge) {
            if ($edge->getParent() === null || count($edge->getChildren()) === 0) {

            }
        }


        //$connections[vertex][graph] = dfg

        //var_dump($tree->getConnections());die;

        return $result;
    }

    public function getTopology(): Topology
    {

    }

    /**
     * @param int ...$outerEdge
     * @return Edge
     * @throws \Exception
     */
    private function splitOnTreeEdges(int ...$outerEdge): Edge
    {
        echo '<h2>new child graph</h2>';
        echo (new Viewer())->vertexAndMatrixToSvg($this->vertexes, $this->matrix);

        if (count($this->vertexes) < 4) {
            return new Edge($this->vertexes);
        }

        if (!count($outerEdge)) {
            $outerVertex = new Vertex('outer');
            $outerVertexId = count($this->vertexes);
            $this->vertexes[] = $outerVertex;
            $edgeVertexes = [rand(0, count($this->vertexes) - 2) => 0];
            $hasOuter = false;
        } else {
            $outerEdge = array_flip($outerEdge);
            $outerVertex = end($this->vertexes);
            $outerVertexId = key($this->vertexes);
            $edgeVertexes = $outerEdge;
            $hasOuter = true;
        }

        $outerVertexes = [$outerVertexId => true];

        foreach ($edgeVertexes as $vertex => $v) {
            $outerVertexes[$vertex] = true;
        }

        $resultChildren = [];
        $first = !$hasOuter;

        global $z;
        if (!isset($z)) {
            $z = 0;
        }

        $needOuter = false;
        $finish = false;

        do {
            foreach ($edgeVertexes as $vertexA => $v) {
                unset($edgeVertexes[$vertexA]);

                if (!$first && !$this->matrix->hasConnectionValue($vertexA, 2)) {
                    continue;
                }

                foreach ($this->matrix->getRow($vertexA) as $vertexB => $value) {
                    if (
                        ($value !== 1 || $needOuter)
                        && ($value !== 2 || !$needOuter)
                    ) {
                        continue;
                    }

                    if ($needOuter) {
                        $finish = true;
                    }

                    echo '<h1>step ' . (++$z)
                        . ' from ' . $this->vertexes[$vertexA]->getName()
                        . ' to ' . $this->vertexes[$vertexB]->getName()
                        . ' path: </h1>';

                    $path = $this->matrix->findShortEdge($vertexA, $vertexB, $first || $finish);
                    $first = false;

                    if ($path === null) {
                        throw new \Exception('Graph is not planar');
                    }

                    echo (new Viewer())->vertexesToSvg($this->valuesToVertexes($path));

                    foreach ($path as $pos => $vertex) {
                        if ($this->matrix->getValue($vertexA, $vertex) === 1 && $pos > 1) {
                            array_splice($path, $pos + 1);

                            break;
                        }
                    }

                    $innerVertexes = $this->getInnerVertexes($path, $outerVertexes);
                    $flipPath = array_flip($path);

                    if (!$needOuter || $hasOuter) {
                        if (count($innerVertexes) === 0) {
                            $resultChildren[] = new Edge($this->keysToVertexes($flipPath));
                        } else {
                            $graph = $this->createSupGraph(...$path, ...$innerVertexes);
                            $graph->vertexes[] = $outerVertex;
                            $vertexes = array_keys($path);
                            $graph->matrix->setEdgeValue($vertexes, 2);
                            $graph->matrix->addEdgeValue($vertexes, count($path) + count($innerVertexes), 3);
                            $resultChildren[] = $graph->splitOnTreeEdges(...$vertexes);
                            echo '<h2>revert to parent</h2>';
                        }
                    } elseif (!count($outerEdge)) {
                        $outerEdge = $flipPath;
                        $hasOuter = true;
                    } else {
                        throw new \Exception('Is not planar graph');
                    }

                    $prevVertex = end($path);

                    foreach ($path as $currentVertex) {
                        $value = $this->matrix->getValue($currentVertex, $prevVertex);

                        if ($value === 2) {
                            $this->matrix->unsetValue($currentVertex, $prevVertex);
                            $this->matrix->unsetValue($prevVertex, $currentVertex);
                        } else {
                            $this->matrix->setValue($currentVertex, $prevVertex, $value + 1);
                            $this->matrix->setValue($prevVertex, $currentVertex, $value + 1);
                        }

                        $prevVertex = $currentVertex;
                        $outerVertexes[$currentVertex] = true;
                    }

                    $this->matrix->addEdgeValue($path, count($this->vertexes) - 1, 3);
                    $this->matrix->deleteValues($innerVertexes);

                    echo '<h3>mutable graph:</h3>';
                    echo (new Viewer())->vertexAndMatrixToSvg($this->vertexes, $this->getMatrix());
                    $edgeVertexes = array_replace($edgeVertexes, $flipPath);
                    //var_dump($edgeVertexes);

                    continue(3);
                }
            }

            $edgeVertexes = $this->matrix->toArray();
            $needOuter = true;
            //var_dump($edgeVertexes);
        } while (!$finish);

        if (!count($outerEdge)) {
            throw new \Exception('Is not planar graph');
        }

        return new Edge($this->keysToVertexes($outerEdge), $resultChildren);
    }

    /**
     * @param array $edge
     * @param int[] $outerVertexes
     * @return int[]
     * @throws \Exception
     */
    private function getInnerVertexes(array $edge, array $outerVertexes): array
    {
        $innerIntersections = $this->getInnerIntersections($edge, $outerVertexes);
        $innerVertexes = [];

        foreach ($innerIntersections as $intersection) {
            $innerVertexes[] = $intersection->getVertexes();
        }

        return count($innerVertexes) ? array_merge(...$innerVertexes) : [];
    }

    /**
     * @param array $edge
     * @param int[] $outerVertexes
     * @return Intersection[]
     */
    private function getIntersections(array $edge, array $outerVertexes): array
    {
        $canvas = $this->canvas->pixel($edge);
        $color = 1;
        $colors = [];
        $intersections = [];

        foreach ($edge as $vertexA) {
            foreach ($this->matrix->getRow($vertexA) as $vertexB => $value) {
                $oldColor = $canvas->getColor($vertexB);

                if ($oldColor !== 1) {
                    $intersections[$oldColor === 0 ? $color + 1 : $oldColor][$vertexA] = true;
                }

                if ($oldColor !== 0) {
                    continue;
                }

                $canvas = $canvas->fill($vertexB, ++$color);
                $colors[$color] = $canvas->getOperations();
            }
        }

        $outerColors = [];

        foreach ($outerVertexes as $outerVertex => $v) {
            $outerColors[$canvas->getColor($outerVertex)] = true;
        }

        $result = [];

        foreach ($colors as $color => $vertexes) {
            $result[] = new Intersection(
                $vertexes,
                $intersections[$color],
                isset($outerColors[$color]) ? true : null
            );
        }

        return $result;
    }

    /**
     * @return Vertex[]
     */
    public function getArticulationVertex(): array
    {
        $result = [];

        foreach ($this->splitGraphOnDisconnected() as $connectedGraphs) {
            $result[] = $connectedGraphs->dfs();
        }

        return $this->valuesToVertexes(array_merge(...$result));
    }

    /**
     * @param self[] $graphs
     * @return self[][]|null
     */
    private function splitByBranches(array &$graphs): ?array
    {
        $articulationVertex = $this->dfs();

        if (!count($articulationVertex)) {
            $graphs[] = $this;

            return null;
        }

        $canvas = $this->canvas->pixel($articulationVertex);
        $result = [];

        echo (new Viewer())->toSvg($articulationVertex, $this->getMatrix());

        foreach ($this->getVertexes() as $indexA => $vertex) {
            if ($canvas->getColor($indexA) !== 0) {
                continue;
            }

            $canvas = $canvas->incFill($indexA, 1);
            $graphs[] = $graph = $this->createSupGraph(...$canvas->getOperations());
            $usedVertexes = array_intersect(array_values($canvas->getOperations()), $articulationVertex);

            foreach ($usedVertexes as $newVertexId => $oldVertexId) {
                $result[$oldVertexId][count($graphs) - 1] = $newVertexId;
            }
        }

        return $result;
    }

    /**
     * @return int[]
     */
    private function dfs(): array
    {
        $children = 0;
        $result = [];
        $number = [];
        $index = [];
        $this->_dfs($number, $index, $result, $children, 0);

        if ($children > 1) {
            $result[0] = 0;
        }

        return $result;
    }

    private function _dfs(array &$number, array &$index, array &$result, int &$children, int $vertexA, int $parentVertex = null): void
    {
        $number[$vertexA] = $index[$vertexA] = $parentVertex === null ? 0 : $number[$parentVertex] + 1;

        foreach ($this->matrix->getRow($vertexA) as $vertexB => $value) {
            if ($vertexB === $parentVertex) {
                continue;
            }

            if (isset($number[$vertexB])) {
                $index[$vertexA] = min($index[$vertexA], $number[$vertexB]);
            } else {
                $this->_dfs($number, $index, $result, $children, $vertexB, $vertexA);
                $index[$vertexA] = min($index[$vertexA], $index[$vertexB]);

                if ($parentVertex === null) {
                    $children++;
                } elseif ($number[$vertexA] <= $index[$vertexB]) {
                    $result[$vertexA] = $vertexA;
                }
            }
        }
    }

    /**
     * @param array $edge
     * @param Intersection[] $intersections
     * @return AdjacencyMatrix
     */
    private function getIntersectionMatrix(array $edge, array $intersections): AdjacencyMatrix
    {
        $matrix = [];

        foreach ($intersections as $number => $intersectionA) {
            for ($i = $number + 1; $i < count($intersections); $i++) {
                $intersectionB = $intersections[$i];

                if ($intersectionA->isConflicted($edge, $intersectionB)) {
                    $matrix[$number][$i] = $matrix[$i][$number] = 1;
                }
            }
        }

        return new AdjacencyMatrix($matrix);
    }

    /**
     * @param array $edge
     * @param int[] $outerVertexes
     * @return Intersection[]
     * @throws \Exception
     */
    private function getInnerIntersections(array $edge, array $outerVertexes): array
    {
        $intersections = $this->getIntersections($edge, $outerVertexes);

        if (count($intersections)) {
            echo '<h3>intersections:</h3>';
            $viewer = new Viewer();

            foreach ($intersections as $intersection) {
                if ($intersection->isOuter()) {
                    echo '<h4>outer</h4>';
                }

                echo $viewer->vertexAndMatrixToSvg(
                    $this->valuesToVertexes(
                        array_merge($intersection->getVertexes(), array_keys($intersection->getConnections()))
                    ),
                    $this->matrix
                );
            }
        }

        $matrix = $this->getIntersectionMatrix($edge, $intersections);

        if (count($intersections)) {
            echo '<h3>intersect matrix:</h3>';
            echo $viewer->matrixToSvg($matrix);
        }

        $knowns = [];
        $unknowns = [];
        $result = [];

        foreach ($intersections as $number => $intersection) {
            if ($intersection->isOuter()) {
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
                        $unknowns[$vertexB]->setIsOuter(!$isOuter);
                        $newKnowns[$vertexB] = !$isOuter;

                        if ($isOuter) {
                            $result[] = $unknowns[$vertexB];
                        }

                        unset($unknowns[$vertexB]);
                    } elseif ($intersections[$vertexB]->isOuter() === $isOuter) {
                        throw new \Exception('graph is not planar');
                    }
                }
            }

            $knowns = $newKnowns;

            if (!count($newKnowns) && count($unknowns)) {
                $unknown = array_slice($unknowns, rand(0, count($unknowns) - 1), 1, true);
                $result[] = reset($unknown);
                $vertexB = key($unknown);
                $knowns[$vertexB] = false;
                $unknowns[$vertexB]->setIsOuter($knowns[$vertexB]);
                unset($unknowns[$vertexB]);
            }
        }

        return $result;
    }

    public function getKeyByNumber(array $data, int $number): ?int
    {
        $keyValue = array_slice($data, $number, 1, true);
        reset($keyValue);

        return key($keyValue);
    }

    public function getNumberByKey(array $data, int $key): ?int
    {
        $pos = 0;

        foreach ($data as $currentKey => $value) {
            if ($key === $currentKey) {
                return $pos;
            }

            $pos++;
        }

        return null;
    }

    public function getMatrix(): AdjacencyMatrix
    {
        return $this->matrix;
    }

    /**
     * @param array $outerEdge
     * @return Vertex[]
     */
    private function keysToVertexes(array $outerEdge): array
    {
        $result = [];

        foreach ($outerEdge as $vertex => $value) {
            $result[] = $this->vertexes[$vertex];
        }

        return $result;
    }

    private function valuesToVertexes(array $outerEdge): array
    {
        $result = [];

        foreach ($outerEdge as $vertex) {
            $result[] = $this->vertexes[$vertex];
        }

        return $result;
    }
}
