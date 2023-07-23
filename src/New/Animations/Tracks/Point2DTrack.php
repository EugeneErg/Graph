<?php

declare(strict_types=1);

namespace EugeneErg\Graph\New\Animations\Tracks;

use EugeneErg\Collections\IntegerCollection;
use EugeneErg\Graph\New\Animations\Collections\Point2DSegmentCollection;
use EugeneErg\Graph\New\Animations\Segments\Point2DSegment;
use EugeneErg\Graph\New\Collections\Point2DCollection;
use EugeneErg\Graph\New\DataTransferObjects\Point2D;

/**
 * @method int addSegment(Point2DSegment $segment, ?int $startMilliSecond = null)
 * @property-read Point2D $defaultValue
 */
class Point2DTrack extends AbstractTrack
{
    public function __construct(Point2D $defaultValue)
    {
        parent::__construct($defaultValue, new Point2DSegmentCollection(immutable: false));
    }

    public function getValues(): Point2DCollection
    {
        return Point2DCollection::fromInstance(parent::getValues());
    }
}
