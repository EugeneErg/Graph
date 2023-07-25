<?php

declare(strict_types=1);

namespace EugeneErg\Graph\New\Animations\Effects;

use EugeneErg\Graph\New\Animations\Collections\AbstractTrackCollection;
use EugeneErg\Graph\New\Animations\Collections\Point2DTrackCollection;
use EugeneErg\Graph\New\Animations\Segments\Point2DSegment;
use EugeneErg\Graph\New\Animations\Tracks\Point2DTrack;
use EugeneErg\Graph\New\DataTransferObjects\Point2D;
use EugeneErg\Graph\New\Services\CoordinateService;
use EugeneErg\Graph\New\ValueObjects\Angle;

class MoveObjectsAroundEffect implements EffectInterface
{
    public readonly Point2D $center;
    public readonly Angle $startAngle;
    public readonly Angle $shiftAngle;

    public function __construct(
        public readonly int $durationMilliSeconds,
        public readonly int $startRadius,
        ?Point2D $center = null,
        ?Angle $startAngle = null,
        ?Angle $shiftAngle = null,
        public readonly int $shiftRadius = 0,
    ) {
        $this->center = $center ?? new Point2D();
        $this->startAngle = $startAngle ?? new Angle();
        $this->shiftAngle = $shiftAngle ?? new Angle();
    }

    /**
     * @param Point2DTrackCollection $tracks
     * @param int|null $startMilliSeconds
     * @return int
     */
    public function apply(AbstractTrackCollection $tracks, ?int $startMilliSeconds = null): int
    {
        $startMilliSeconds = $startMilliSeconds ?? $tracks->reduce(
            fn (int $result, Point2DTrack $track): int => max($result, $track->getDurationMilliSecond()),
            0,
        );
        $radius = $this->startRadius;
        $angle = $this->startAngle;

        foreach ($tracks as $point) {
            $point->addSegment(
                new Point2DSegment($this->durationMilliSeconds, CoordinateService::getPoint($radius, $angle)->plus($this->center)),
                $startMilliSeconds,
            );
            $radius += $this->shiftRadius;
            $angle = $angle->plus($this->shiftAngle);
        }

        return $startMilliSeconds + $this->durationMilliSeconds;
    }
}
