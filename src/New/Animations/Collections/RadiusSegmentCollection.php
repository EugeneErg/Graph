<?php

declare(strict_types=1);

namespace EugeneErg\Graph\New\Animations\Collections;

use EugeneErg\Graph\New\Animations\Segments\IntegerSegment;

class RadiusSegmentCollection extends AbstractSegmentCollection
{
    protected const VALUE_TYPE = IntegerSegment::class;
}