<?php declare(strict_types=1);
namespace EugeneErg\Graph\Collections;

use EugeneErg\Graph\ValueObjects\Intersection;
use Generator;

/**
 * @method Intersection[]|Generator getIterator()
 */
class IntersectionCollection extends AbstractLineCollection
{
    protected const ELEMENT_CLASS = Intersection::class;
}
