<?php declare(strict_types = 1);
namespace EugeneErg\Graph\Collections;

use EugeneErg\Graph\ValueObjects\Vertex;

/**
 * @method Vertex offsetGet(int|string $offset)
 * @method Vertex|null current()
 * @method Vertex|null next()
 * @method Vertex|null rewind()
 * @method __construct(Vertex[] $records)
 * @method Vertex[] toArray()
 */
class VertexesCollection extends AbstractCollection
{
    public static function isValidElement($value): bool
    {
        return $value instanceof Vertex;
    }
}
