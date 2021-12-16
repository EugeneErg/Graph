<?php declare(strict_types=1);
namespace EugeneErg\Graph\Collections;

/**
 * @method bool offsetGet(int|string $offset)
 * @method __construct(bool[] $items)
 * @method bool[] getIterator()
 * @method bool[] toArray()
 */
class BoolCollection extends AbstractLineCollection
{
    protected const ELEMENT_CLASS = 'boolean';
}
