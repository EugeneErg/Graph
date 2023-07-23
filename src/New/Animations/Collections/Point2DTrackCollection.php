<?php

declare(strict_types=1);

namespace EugeneErg\Graph\New\Animations\Collections;

use EugeneErg\Collections\ObjectCollection;
use EugeneErg\Graph\New\Animations\Tracks\Point2DTrack;

/**
 * @method Point2DTrack offsetGet(mixed $offset)
 * @method Point2DTrack[] getIterator()
 * @method self reverse(bool $preserveKeys = false)
 */
class Point2DTrackCollection extends AbstractTrackCollection
{
    protected const VALUE_TYPE = Point2DTrack::class;
}