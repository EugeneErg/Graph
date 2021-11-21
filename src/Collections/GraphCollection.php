<?php declare(strict_types = 1);
namespace EugeneErg\Graph\Collections;

use EugeneErg\Graph\ValueObjects\Graph;

/**
 * @method Graph offsetGet(int|string $offset)
 * @method __construct(Graph[] $items)
 * @method Graph[] getIterator()
 * @method Graph[] toArray()
 */
class GraphCollection extends AbstractCollection
{
    protected const ELEMENT_CLASS = Graph::class;
}
