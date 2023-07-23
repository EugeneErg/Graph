<?php

declare(strict_types=1);

namespace EugeneErg\Graph\New\Collections;

use EugeneErg\Collections\ObjectCollection;
use EugeneErg\Graph\New\DataTransferObjects\Point2D;

class Point2DCollection extends ObjectCollection
{
    protected const VALUE_TYPE = Point2D::class;
}