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
    public static function isValidElement($value): bool
    {
        AssertService::instance()->type(IntegerCollection::class, $value);

        return true;
    }

    public static function isValidKey($key): bool
    {
        return is_integer($key);
    }

    protected function createEmptyElement($key): IntegerCollection
    {
        return new IntegerCollection();
    }
}

