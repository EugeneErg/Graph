<?php declare(strict_types=1);
namespace EugeneErg\Graph\Collections;

use EugeneErg\Graph\Dto\Point2D;

class Point2DCollection extends AbstractLineCollection
{
    protected const ELEMENT_CLASS = Point2D::class;
}
