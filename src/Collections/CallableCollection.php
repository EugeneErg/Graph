<?php declare(strict_types=1);
namespace EugeneErg\Graph\Collections;

/**
 * @method callable offsetGet(int|string $offset)
 * @method __construct(callable[] $items)
 * @method callable[] getIterator()
 * @method callable[] toArray()
 */
class CallableCollection extends AbstractLineCollection
{
    protected const ELEMENT_CLASS = 'callable';
}
