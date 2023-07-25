<?php

declare(strict_types=1);

namespace EugeneErg\Graph\New\Animations\Tracks;

use EugeneErg\Graph\New\Animations\Collections\ColorSegmentCollection;
use EugeneErg\Graph\New\Animations\Segments\ColorSegment;

/**
 * @method int addSegment(ColorSegment $segment, ?int $startMilliSecond = null)
 */
class ColorTrack extends AbstractTrack
{
    public function __construct(string $defaultValue)
    {
        parent::__construct($defaultValue, new ColorSegmentCollection());
    }
}