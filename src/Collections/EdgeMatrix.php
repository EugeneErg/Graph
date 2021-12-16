<?php declare(strict_types=1);
namespace EugeneErg\Graph\Collections;

/**
 * @method EdgeCollection offsetGet(int|string $offset)
 * @method __construct(EdgeCollection[] $items)
 * @method EdgeCollection[] getIterator()
 * @method EdgeCollection[] toArray()
 */
class EdgeMatrix extends AbstractMatrix2
{
    protected const ELEMENT_CLASS = EdgeCollection::class;
}
