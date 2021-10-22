<?php declare(strict_types = 1);
namespace EugeneErg\Graph\Collections;

use EugeneErg\Graph\Services\AssertService;
use EugeneErg\Graph\ValueObjects\Edge;

/**
 * @method Edge offsetGet(int|string $offset)
 * @method Edge|null current()
 * @method Edge|null next()
 * @method Edge|null rewind()
 * @method __construct(Edge[] $items)
 * @method Edge[] toArray()
 */
class EdgeCollection extends AbstractCollection
{
    public static function isValidElement($value): bool
    {
        AssertService::instance()->type(Edge::class, $value);

        return true;
    }
}