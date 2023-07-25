<?php

declare(strict_types=1);

namespace EugeneErg\Graph\New\Animations\Collections;

use EugeneErg\Graph\New\Animations\Tracks\ColorTrack;

class ColorTrackCollection extends AbstractTrackCollection
{
    protected const VALUE_TYPE = ColorTrack::class;
}