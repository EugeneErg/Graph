<?php

declare(strict_types=1);

namespace EugeneErg\Graph\New\Animations\Collections;

use EugeneErg\Collections\ObjectCollection;
use EugeneErg\Graph\New\Animations\Tracks\AbstractTrack;

/**
 * @method AbstractTrack offsetGet(mixed $offset)
 * @method AbstractTrack[] getIterator()
 * @method self reverse(bool $preserveKeys = false)
 */
class AbstractTrackCollection extends ObjectCollection
{
    protected const VALUE_TYPE = AbstractTrack::class;
}