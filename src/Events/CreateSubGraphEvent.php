<?php declare(strict_types=1);
namespace EugeneErg\Graph\Events;

use EugeneErg\Graph\ValueObjects\ClearGraph;

class CreateSubGraphEvent
{
    private ClearGraph $graph;

    public function __construct(ClearGraph $graph)
    {
        $this->graph = $graph;
    }

    public function getGraph(): ClearGraph
    {
        return $this->graph;
    }
}
