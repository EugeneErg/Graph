<?php declare(strict_types = 1);
namespace EugeneErg\Graph\Collections;

use EugeneErg\Graph\ValueObjects\Edge;

/**
 * @method Edge offsetGet(int|string $offset)
 * @method Edge|null current()
 * @method Edge|null next()
 * @method Edge|null rewind()
 * @method __construct(Edge[] $items)
 * @method Edge[] toArray()
 */
class EdgeCollection extends AbstractCollection
{
    protected const ELEMENT_CLASS = Edge::class;
}