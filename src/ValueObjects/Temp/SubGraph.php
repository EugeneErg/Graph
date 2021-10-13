<?php namespace EugeneErg\Graph\ValueObjects\Temp;

use EugeneErg\Graph\ValueObjects\Edge;

class SubGraph extends AbstractTempDto
{
    public $edges;
    public $counter;

    /**
     * @param Edge[] $edges
     * @param Edge $counter
     */
    public function __construct(Edge $counter, array $edges = [])
    {
        $this->edges = $edges;
        $this->counter = $counter;
    }
}
