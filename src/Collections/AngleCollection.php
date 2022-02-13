<?php declare(strict_types=1);
namespace EugeneErg\Graph\Collections;

use EugeneErg\Graph\ValueObjects\Angle;

class AngleCollection extends AbstractLineCollection
{
    protected const ELEMENT_CLASS = Angle::class;
}
