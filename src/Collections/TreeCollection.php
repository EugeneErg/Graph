<?php declare(strict_types=1);

namespace EugeneErg\Graph\Collections;

use EugeneErg\Graph\ValueObjects\Tree;

/**
 * @method Tree offsetGet(int|string $offset)
 * @method __construct(Tree[] $items)
 * @method Tree[] getIterator()
 * @method Tree[] toArray()
 */
class TreeCollection extends AbstractLineCollection
{
    protected const ELEMENT_CLASS = Tree::class;
}
