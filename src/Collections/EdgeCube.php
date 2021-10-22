<?php declare(strict_types=1);
namespace EugeneErg\Graph\Collections;

use EugeneErg\Graph\Services\AssertService;

/**
 * @method EdgeMatrix offsetGet(int|string $offset)
 * @method EdgeMatrix|null current()
 * @method EdgeMatrix|null next()
 * @method EdgeMatrix|null rewind()
 * @method __construct(EdgeMatrix[] $items)
 * @method EdgeMatrix[] toArray()
 */
class EdgeCube extends AbstractMatrix
{
    public static function isValidElement($value): bool
    {
        AssertService::instance()->type(EdgeMatrix::class, $value);

        return true;
    }

    protected function createEmptyElement($key): EdgeMatrix
    {
        return new EdgeMatrix();
    }
}
