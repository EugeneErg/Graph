<?php declare(strict_types = 1);
namespace EugeneErg\Graph\ValueObjects\Temp;

use EugeneErg\Graph\Collections\EdgeCollection;
use EugeneErg\Graph\ValueObjects\Edge;

class SubGraph extends AbstractTempDto
{
    public $edges;
    public $counter;

    public function __construct(Edge $counter, EdgeCollection $edges = null)
    {
        $this->edges = $edges ?? new EdgeCollection();
        $this->counter = $counter;
    }
}
