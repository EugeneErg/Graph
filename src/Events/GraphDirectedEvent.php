<?php declare(strict_types=1);
namespace EugeneErg\Graph\Events;

use EugeneErg\Graph\ValueObjects\Graph;

class GraphDirectedEvent
{
    private Graph $graph;

    public function __construct(Graph $graph)
    {
        $this->graph = $graph;
    }

    public function getGraph(): Graph
    {
        return $this->graph;
    }
}
