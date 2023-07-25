<?php

declare(strict_types=1);

namespace EugeneErg\Graph\New\Animations\Tracks;

use EugeneErg\Collections\IntegerCollection;
use EugeneErg\Graph\New\Animations\Collections\RadiusSegmentCollection;
use EugeneErg\Graph\New\Animations\Segments\RadiusSegment;

/**
 * @method int addSegment(RadiusSegment $segment, ?int $startMilliSecond = null)
 */
class RadiusTrack extends AbstractTrack
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