<?php declare(strict_types=1);

namespace EugeneErg\Graph\Events;

use EugeneErg\Graph\Collections\EdgeCollection;
use EugeneErg\Graph\ValueObjects\Edge;

class UnionEdgesEvent
{
    private Edge $edgeA;
    private Edge $edgeB;
    private EdgeCollection $edges;

    public function __construct(Edge $edgeA, Edge $edgeB, EdgeCollection $edges)
    {
        $this->edgeA = $edgeA;
        $this->edgeB = $edgeB;
        $this->edges = $edges;
    }

    public function getEdgeA(): Edge
    {
        return $this->edgeA;
    }

    public function getEdgeB(): Edge
    {
        return $this->edgeB;
    }

    public function getEdges(): EdgeCollection
    {
        return $this->edges;
    }
}
