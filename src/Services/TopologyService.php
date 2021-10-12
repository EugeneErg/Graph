<?php namespace EugeneErg\Graphs\Services;

use EugeneErg\Graphs\ValueObjects\ClearGraph;
use EugeneErg\Graphs\ValueObjects\Topology;

class TopologyService
{
    /** @var GraphService */
    private $graphService;
    /** @var EdgeService */
    private $edgeService;

    public function __construct()
    {
        $this->graphService = new GraphService();
        $this->edgeService = new EdgeService();
    }
}