<?php

declare(strict_types=1);

namespace EugeneErg\Graph\New\Services;

use EugeneErg\Collections\IntegerCollection;
use EugeneErg\Graph\New\ValueObjects\Graph;

class ArticulationVertexesFinderService
{
    private int $children;
    private IntegerCollection $result;
    private IntegerCollection $number;
    private IntegerCollection$index;
    private Graph $graph;

    public function getArticulationVertexesInConnectedGraph(Graph $graph): IntegerCollection
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

        foreach ($this->graph->getColumn($vertexA) ?? [] as $vertexB => $value) {
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

    private function prepare(Graph $graph): void
    {
        $this->graph = $graph;
        $this->children = 0;
        $this->result = new IntegerCollection(immutable: false);
        $this->number = new IntegerCollection(immutable: false);
        $this->index = new IntegerCollection(immutable: false);
    }
}
