<?php declare(strict_types = 1);
namespace EugeneErg\Graph\Collections;

/**
 * @method int offsetGet(int|string $offset)
 * @method int|null current()
 * @method int|null next()
 * @method int|null rewind()
 * @method __construct(int[] $records)
 * @method int[] toArray()
 */
class IntegerCollection extends AbstractCollection
{
    public static function isValidElement($value): bool
    {
        return is_integer($value);
    }
}
