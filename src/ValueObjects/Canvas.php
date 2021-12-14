<?php declare(strict_types = 1);
namespace EugeneErg\Graph\ValueObjects;

use EugeneErg\Graph\Collections\IntegerCollection;

/**
 * @see Canvas::getGraph()
 * @property-read AbstractGraph $graph
 */
class Canvas extends IntegerCollection
{
    private $graph;

    public function __construct(AbstractGraph $graph)
    {
        $this->graph = $graph;
        parent::__construct();
    }

    public function offsetGet($offset): int
    {
        return $this->offsetExists($offset) ? parent::offsetGet($offset) : 0;
    }

    public function getGraph(): AbstractGraph
    {
        return $this->graph;
    }
}
