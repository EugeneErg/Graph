<?php declare(strict_types=1);

namespace EugeneErg\Graph\Collections;

use EugeneErg\Graph\ValueObjects\Temp\SubGraph;

/**
 * @method SubGraph shift()
 */
class SubGraphCollection extends AbstractLineCollection
{
    protected const ELEMENT_CLASS = SubGraph::class;
}
