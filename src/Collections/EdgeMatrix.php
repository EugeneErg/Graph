<?php declare(strict_types=1);
namespace EugeneErg\Graph\Collections;

use EugeneErg\Graph\Services\AssertService;

/**
 * @method EdgeCollection offsetGet(int|string $offset)
 * @method EdgeCollection|null current()
 * @method EdgeCollection|null next()
 * @method EdgeCollection|null rewind()
 * @method __construct(EdgeCollection[] $items)
 * @method EdgeCollection[] toArray()
 */
class EdgeMatrix extends AbstractMatrix
{
    public static function isValidElement($value): bool
    {
        AssertService::instance()->type(EdgeCollection::class, $value);

        return true;
    }

    protected function createEmptyElement($key): EdgeCollection
    {
        return new EdgeCollection();
    }
}
