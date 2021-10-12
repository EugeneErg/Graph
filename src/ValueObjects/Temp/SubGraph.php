<?php namespace EugeneErg\Graphs\ValueObjects\Temp;

use EugeneErg\Graphs\ValueObjects\Edge;

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
