<?php declare(strict_types=1);
namespace EugeneErg\Graph\Events;

use EugeneErg\Graph\Collections\IntegerCollection;

class ConnectedGraphFoundEvent
{
    private IntegerCollection $vertexes;

    public function __construct(IntegerCollection $vertexes)
    {
        $this->vertexes = $vertexes;
    }

    public function getVertexes(): IntegerCollection
    {
        return $this->vertexes;
    }
}
