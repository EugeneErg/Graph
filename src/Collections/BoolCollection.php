<?php declare(strict_types=1);
namespace EugeneErg\Graph\Collections;

use EugeneErg\Graph\Services\AssertService;

/**
 * @method bool offsetGet(int|string $offset)
 * @method bool|null current()
 * @method bool|null next()
 * @method bool|null rewind()
 * @method __construct(bool[] $items)
 * @method bool[] toArray()
 */
class BoolCollection extends AbstractCollection
{
    public static function isValidElement($value): bool
    {
        AssertService::instance()->type('bool', $value);

        return true;
    }
}
