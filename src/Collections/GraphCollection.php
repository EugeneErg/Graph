<?php declare(strict_types = 1);
namespace EugeneErg\Graph\Collections;

use EugeneErg\Graph\Services\AssertService;
use EugeneErg\Graph\ValueObjects\Graph;

/**
 * @method Graph offsetGet(int|string $offset)
 * @method Graph|null current()
 * @method Graph|null next()
 * @method Graph|null rewind()
 * @method __construct(Graph[] $items)
 * @method Graph[] toArray()
 */
class GraphCollection extends AbstractCollection
{
    public static function isValidElement($value): bool
    {
        AssertService::instance()->type(Graph::class, $value);

        return true;
    }
}
