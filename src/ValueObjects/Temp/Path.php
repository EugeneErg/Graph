<?php namespace EugeneErg\Graph\ValueObjects\Temp;

class Path extends AbstractTempDto
{
    /** @var int[] */
    public $old;//замещаемые вершины,
    /** @var int[] */
    public $new;//новые вершины
    /** @var Step[] */
    public $steps;
    /** @var SubGraph[] */
    public $graphs;
    /** @var int[][] */
    public $fields;

    public function __construct()
    {
        $this->steps = [];
        $this->old = [];
        $this->new = [];
        $this->graphs = [];
        $this->fields = [];
    }
}