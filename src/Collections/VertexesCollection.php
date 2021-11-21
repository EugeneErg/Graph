<?php declare(strict_types = 1);
namespace EugeneErg\Graph\Collections;

use EugeneErg\Graph\ValueObjects\Vertex;

/**
 * @method Vertex offsetGet(int|string $offset)
 * @method __construct(Vertex[] $items)
 * @method Vertex[] getIterator()
 * @method Vertex[] toArray()
 */
class VertexesCollection extends AbstractCollection
{
    protected const ELEMENT_CLASS = Vertex::class;
}
