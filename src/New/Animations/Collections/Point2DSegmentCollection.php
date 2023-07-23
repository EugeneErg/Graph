<?php

declare(strict_types=1);

namespace EugeneErg\Graph\New\Animations\Collections;

use EugeneErg\Graph\New\Animations\Segments\Point2DSegment;

class Point2DSegmentCollection extends AbstractSegmentCollection
{
    protected const VALUE_TYPE = Point2DSegment::class;
}