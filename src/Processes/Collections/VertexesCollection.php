<?php declare(strict_types=1);

namespace EugeneErg\Graph\Processes\Collections;

use EugeneErg\Graph\Collections\AbstractLineCollection;
use EugeneErg\Graph\Processes\SvgAnimation\ValueObject\Vertex;

/**
 * @method Vertex offsetGet(int|string $offset)
 * @method Vertex[] getIterator()
 */
class VertexesCollection extends AbstractLineCollection
{
    protected const ELEMENT_CLASS = Vertex::class;
}
