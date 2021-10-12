<?php namespace EugeneErg\Graphs\Services;

use EugeneErg\Graphs\ValueObjects\ClearGraph;

class ArticulationVertexesFinderService
{
    private $children;
    private $result;
    private $number;
    private $index;
    private $graph;
    private $addIndex;

    /**
     * @param ClearGraph $graph
     * @return int[]
     */
    public function getArticulationVertexesInConnectedGraph(ClearGraph $graph): array
    {
        $this->refresh($graph);

        return $this->result;
    }

    private function refresh(ClearGraph $graph): void
    {
        $this->graph = $graph;
        $this->children = 0;
        $this->result = [];
        $this->number = [];
        $this->index = [];
        $this->addIndex = [];
        $this->dfs($graph->vertexes[0]);

        if ($this->children > 1) {
            $this->result[$graph->vertexes[0]] = $graph->vertexes[0];
        }
    }

    private function dfs(int $vertexA, int $parentVertex = null): void
    {
        $this->number[$vertexA]
            = $this->index[$vertexA]
            = $parentVertex === null ? 0 : $this->number[$parentVertex] + 1;

        foreach ($this->graph->getRow($vertexA) as $vertexB => $value) {
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
}
