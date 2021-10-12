<?php namespace EugeneErg\Graphs;

class Tree
{
    /** @var Graph */
    private $graph;
    /* @var Graph[] */
    private $branches;
    /** @var int[][] */
    private $connections;

    /**
     * Tree constructor.
     * @param Graph $graph
     * @param Graph[] $branches
     * @param int[][] $connections
     */
    public function __construct(Graph $graph, array $branches, array $connections)
    {
        $this->graph = $graph;
        $this->branches = $branches;
        $this->connections = $connections;
    }

    /**
     * @return Graph[]
     */
    public function getBranches(): array
    {
        return $this->branches;
    }

    /**
     * @return int[][]
     */
    public function getConnections(): array
    {
        return $this->connections;
    }
}