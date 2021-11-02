<?php declare(strict_types = 1);
namespace EugeneErg\Graph\Services;

use EugeneErg\Graph\Collections\IntegerCollection;
use EugeneErg\Graph\ValueObjects\ClearGraph;
use EugeneErg\Graph\ValueObjects\Graph;

class ArticulationVertexesFinderService extends AbstractService
{
    /** @var int */
    private $children;
    /** @var IntegerCollection */
    private $result;
    /** @var IntegerCollection */
    private $number;
    /** @var IntegerCollection */
    private $index;
    /** @var Graph */
    private $graph;

    public function getArticulationVertexesInConnectedGraph(ClearGraph $graph): IntegerCollection
    {
        $this->prepare($graph);
        $this->refresh();

        return $this->result;
    }

    private function refresh(): void
    {
        $this->dfs($this->graph->vertexes[0]);

        if ($this->children > 1) {
            $this->result[$this->graph->vertexes[0]] = $this->graph->vertexes[0];
        }
    }

    private function dfs(int $vertexA, int $parentVertex = null): void
    {
        $this->number[$vertexA]
            = $this->index[$vertexA]
            = $parentVertex === null ? 0 : $this->number[$parentVertex] + 1;

        foreach ($this->graph->connections[$vertexA] ?? [] as $vertexB => $value) {
            if ($vertexB === $parentVertex) {
                continue;
            }

            if (isset($this->number[$vertexB])) {
                $this->index[$vertexA] = min($this->index[$vertexA], $this->number[$vertexB]);
            } else {
                $this->dfs($vertexB, $vertexA);
                $this->index[$vertexA] = min($this->index[$vertexA], $this->index[$vertexB]);

                if ($parentVertex === null) {
                    $this->children++;
                } elseif ($this->number[$vertexA] <= $this->index[$vertexB]) {
                    $this->result[$vertexA] = $vertexA;
                }
            }
        }
    }

    private function prepare(ClearGraph $graph): void
    {
        $this->graph = $graph;
        $this->children = 0;
        $this->result = new IntegerCollection();
        $this->number = new IntegerCollection();
        $this->index = new IntegerCollection();
    }
}
