<?php declare(strict_types=1);
namespace EugeneErg\Graph\Collections;

/**
 * @method bool offsetGet(int|string $offset)
 * @method bool|null current()
 * @method bool|null next()
 * @method bool|null rewind()
 * @method __construct(bool[] $items)
 * @method bool[] getIterator()
 * @method bool[] toArray()
 */
class BoolCollection extends AbstractCollection
{
    protected const ELEMENT_CLASS = 'bool';
}
