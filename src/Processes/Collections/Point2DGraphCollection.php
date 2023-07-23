<?php declare(strict_types = 1);
namespace EugeneErg\Graph\Processes\Collections;

use EugeneErg\Graph\Collections\AbstractLineCollection;
use EugeneErg\Graph\Processes\SvgAnimation\ValueObjects\Point2DGraph;

class Point2DGraphCollection extends AbstractLineCollection
{
    protected const ELEMENT_CLASS = Point2DGraph::class;
}
