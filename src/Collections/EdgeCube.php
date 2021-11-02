<?php declare(strict_types=1);
namespace EugeneErg\Graph\Collections;

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
    protected const ELEMENT_CLASS = EdgeMatrix::class;
}
