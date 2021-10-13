<?php declare(strict_types = 1);
namespace EugeneErg\Graph\ValueObjects;

/**
 * @property-read int[] $vertexes
 * @property-read int $start
 * @property-read int $length
 * @see Replacement::getFirstVertexAttribute()
 * @property-read int $firstVertex
 * @see Replacement::getLastVertexAttribute()
 * @property-read int $lastVertex
 */
class Replacement extends AbstractValueObjectMutable
{
    public function __construct(array $vertexes, int $start, int $length)
    {
        parent::__construct($vertexes, $start, $length);
    }

    protected function getFirstVertexAttribute(object $attributes): int
    {
        return reset($attributes->vertexes);
    }

    protected function getLastVertexAttribute(object $attributes): int
    {
        return end($attributes->vertexes);
    }
}