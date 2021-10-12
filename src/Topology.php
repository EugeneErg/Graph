<?php namespace EugeneErg\Graphs;

class Topology
{
    private $edge;
    private $arcs;

    /**
     * Topology constructor.
     * @param Edge $edge
     * @param Arc[] $arcs
     */
    public function __construct(Edge $edge, array $arcs)
    {
        $this->edge = $edge;
        $this->arcs = $arcs;
    }
}