<?php declare(strict_types = 1);
namespace EugeneErg\Graph\Collections;

use EugeneErg\Graph\ValueObjects\Edge;
use Traversable;

/**
 * @method Edge offsetGet(int|string $offset)
 * @method __construct(Edge[] $items)
 * @method Edge[] getIterator()
 * @method Edge[] toArray()
 * @method Edge[] getUpdatingIterator()
 * @method Edge shift()
 */
class EdgeCollection extends AbstractLineCollection
{
    protected const ELEMENT_CLASS = Edge::class;
}