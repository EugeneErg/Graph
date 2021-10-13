<?php namespace EugeneErg\Graph\ValueObjects;

/**
 * @property-read Graph $graph
 * @property-read Graph[] $branches
 * @property-read Graph $connections
 */
class Tree extends AbstractValueObjectMutable
{
    /**
     * @param Graph $graph
     * @param Graph[] $branches
     * @param int[][] $connections
     */
    public function __construct(Graph $graph, array $branches = [], array $connections = [])
    {
        $matrix = [];

        foreach ($connections as $vertex => $subBranches) {
            foreach ($subBranches as $branchA) {
                foreach ($subBranches as $branchB) {
                    if ($branchA !== $branchB) {
                        $matrix[$branchA][$branchB] = $vertex;
                    }
                }
            }
        }

        parent::__construct($graph, $branches, new Graph($matrix));
    }

    public function hasConnection(int $vertex, int $branch): bool
    {
        return isset($this->connections[$vertex][$branch]);
    }
}