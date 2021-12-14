<?php declare(strict_types = 1);
namespace EugeneErg\Graph\ValueObjects;

use EugeneErg\Graph\Collections\GraphCollection;
use EugeneErg\Graph\Collections\IntegerCollection;
use EugeneErg\Graph\Collections\IntegerMatrix;

/**
 * @see Tree::getGraph()
 * @property-read AbstractGraph $graph
 * @see Tree::getBranches()
 * @property-read GraphCollection $branches
 * @see Tree::getConnections()
 * @property-read Graph $connections
 */
class Tree extends AbstractValueObject
{
    private $graph;
    private $branches;
    private $connections;

    public function __construct(AbstractGraph $graph, ?GraphCollection $branches = null, ?IntegerMatrix $connections = null)
    {
        $this->graph = $graph;
        $this->branches = $branches ?? new GraphCollection();
        $this->connections = new Graph(new IntegerMatrix());

        foreach ($connections ?? [] as $vertex => $subBranches) {
            foreach ($subBranches as $branchA) {
                foreach ($subBranches as $branchB) {
                    $this->connections->setCell($branchA, $branchB, $vertex);
                }
            }
        }
    }

    public function getBranches(): GraphCollection
    {
        return $this->branches;
    }

    public function getConnections(): Graph
    {
        return $this->connections;
    }

    public function getGraph(): AbstractGraph
    {
        return $this->graph;
    }

    public function toArray(): array
    {
        return [
            'graph' => $this->graph,
            'branches' => $this->branches,
            'connections' => $this->connections,
        ];
    }
}