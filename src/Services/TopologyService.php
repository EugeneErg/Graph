<?php declare(strict_types = 1);
namespace EugeneErg\Graph\Services;

use EugeneErg\Graph\ValueObjects\ClearGraph;
use EugeneErg\Graph\ValueObjects\Topology;

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