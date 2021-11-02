<?php declare(strict_types = 1);
namespace EugeneErg\Graph\Collections;

use EugeneErg\Graph\Services\AssertService;

/**
 * @method IntegerCollection offsetGet(int $offset)
 * @method IntegerCollection|null current()
 * @method IntegerCollection|null next()
 * @method IntegerCollection|null rewind()
 * @method __construct(IntegerCollection[] $items)
 * @method IntegerCollection[] toArray()
 */
class IntegerMatrix extends AbstractMatrix
{
    protected const ELEMENT_CLASS = IntegerCollection::class;

    public static function validateKey($key): void
    {
        AssertService::instance()->type('integer', $key);
    }
}

