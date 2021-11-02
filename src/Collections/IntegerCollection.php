<?php declare(strict_types = 1);
namespace EugeneErg\Graph\Collections;

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
    protected const ELEMENT_CLASS = 'integer';
}
