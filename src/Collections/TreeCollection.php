<?php declare(strict_types=1);

namespace EugeneErg\Graph\Collections;

use EugeneErg\Graph\ValueObjects\Tree;

/**
 * @method Tree offsetGet(int|string $offset)
 * @method Tree|null current()
 * @method Tree|null next()
 * @method Tree|null rewind()
 * @method __construct(Tree[] $items)
 * @method Tree[] toArray()
 */
class TreeCollection extends AbstractCollection
{
    protected const ELEMENT_CLASS = Tree::class;
}
