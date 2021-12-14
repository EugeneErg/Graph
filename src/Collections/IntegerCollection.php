<?php declare(strict_types = 1);
namespace EugeneErg\Graph\Collections;

/**
 * @method int offsetGet(int|string $offset)
 * @method __construct(int[] $items = [])
 * @method ArrayIterator<int> getIterator()
 * @method int[] toArray()
 */
class IntegerCollection extends AbstractLineCollection
{
    protected const ELEMENT_CLASS = 'integer';
}
