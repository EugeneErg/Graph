<?php declare(strict_types = 1);
namespace EugeneErg\Graph\Collections;

use EugeneErg\Graph\Services\AssertService;

/**
 * @method int offsetGet(int|string $offset)
 * @method int|null current()
 * @method int|null next()
 * @method int|null rewind()
 * @method __construct(int[] $items)
 * @method int[] toArray()
 */
class IntegerCollection extends AbstractCollection
{
    public static function isValidElement($value): bool
    {
        AssertService::instance()->type('integer', $value);

        return true;
    }
}
