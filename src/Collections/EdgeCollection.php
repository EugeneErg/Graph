<?php declare(strict_types = 1);
namespace EugeneErg\Graph\Collections;

use EugeneErg\Graph\ValueObjects\Edge;

/**
 * @method Edge offsetGet(int|string $offset)
 * @method __construct(Edge[] $items)
 * @method Edge[] getIterator()
 * @method Edge[] toArray()
 */
class EdgeCollection extends AbstractCollection
{
    protected const ELEMENT_CLASS = Edge::class;
}