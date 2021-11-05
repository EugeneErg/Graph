<?php declare(strict_types=1);
namespace EugeneErg\Graph\Collections;

use EugeneErg\Graph\ValueObjects\Edge;

/**
 * @method EdgeMatrix offsetGet(int|string $offset)
 * @method EdgeMatrix|null current()
 * @method EdgeMatrix|null next()
 * @method EdgeMatrix|null rewind()
 * @method __construct(EdgeMatrix[] $items)
 * @method EdgeMatrix[] getIterator()
 * @method EdgeMatrix[] toArray()
 */
class EdgeCube extends AbstractMatrix
{
    protected const ELEMENT_CLASS = EdgeMatrix::class;
}
