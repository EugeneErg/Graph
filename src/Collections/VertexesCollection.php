<?php declare(strict_types = 1);
namespace EugeneErg\Graph\Collections;

use EugeneErg\Graph\Services\AssertService;
use EugeneErg\Graph\ValueObjects\Vertex;

/**
 * @method Vertex offsetGet(int|string $offset)
 * @method Vertex|null current()
 * @method Vertex|null next()
 * @method Vertex|null rewind()
 * @method __construct(Vertex[] $items)
 * @method Vertex[] toArray()
 */
class VertexesCollection extends AbstractCollection
{
    public static function isValidElement($value): bool
    {
        AssertService::instance()->type(Vertex::class, $value);

        return true;
    }
}
