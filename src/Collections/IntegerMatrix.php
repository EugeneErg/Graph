<?php declare(strict_types = 1);
namespace EugeneErg\Graph\Collections;

/**
 * @method IntegerCollection offsetGet(int $offset)
 * @method IntegerCollection|null current()
 * @method IntegerCollection|null next()
 * @method IntegerCollection|null rewind()
 * @method __construct(IntegerCollection[] $records)
 * @method IntegerCollection[] toArray()
 */
class IntegerMatrix extends AbstractCollection
{
    public static function isValidElement($value): bool
    {
        return $value instanceof IntegerCollection;
    }

    public static function isValidKey($key): bool
    {
        return is_integer($key);
    }
}
