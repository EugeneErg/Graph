<?php

declare(strict_types=1);

namespace EugeneErg\Graph\New\Animations\Tracks;

use EugeneErg\Collections\IntegerCollection;
use EugeneErg\Graph\New\Animations\Collections\RadiusSegmentCollection;
use EugeneErg\Graph\New\Animations\Segments\IntegerSegment;

/**
 * @method int addSegment(IntegerSegment $segment, ?int $startMilliSecond = null)
 */
class IntegerTrack extends AbstractTrack
{
    public function __construct(int $defaultValue)
    {
        parent::__construct($defaultValue, new RadiusSegmentCollection());
    }

    public function getValues(): IntegerCollection
    {
        return IntegerCollection::fromInstance(parent::getValues());
    }
}