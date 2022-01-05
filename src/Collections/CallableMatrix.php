<?php declare(strict_types=1);

namespace EugeneErg\Graph\Collections;

/**
 * @method CallableCollection offsetGet(int $offset)
 * @method __construct(CallableCollection[] $items)
 * @method CallableCollection[] getIterator()
 * @method CallableCollection[] toArray()
 */
class CallableMatrix extends AbstractMatrix2
{
    protected const ELEMENT_CLASS = CallableCollection::class;
}
