<?php namespace EugeneErg\Graphs\ValueObjects;

/**
 * @property-read  Graph $graph
 * @see Canvas::setVertexesAttribute()
 * @property int[] $vertexes
 * @see Canvas::addVertexAttribute()
 * @method int addVertex(int $vertex, int $color)
 */
class Canvas extends AbstractValueObjectMutable
{
    public function __construct(Graph $graph, array $vertexes = [])
    {
        parent::__construct($graph, $vertexes);
    }

    public function setVertexesAttribute(Object $attributes, array $vertexes): void
    {
        $attributes->vertexes = $vertexes;
    }

    public function getColor(int $vertex): int
    {
        return $this->vertexes[$vertex] ?? 0;
    }

    public function addVertexAttribute(Object $attributes, int $vertex, int $color): void
    {
        $attributes->vertexes[$vertex] = $color;
    }
}
