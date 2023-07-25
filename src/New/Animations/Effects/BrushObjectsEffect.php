<?php

declare(strict_types=1);

namespace EugeneErg\Graph\New\Animations\Effects;

use EugeneErg\Graph\New\Animations\Collections\AbstractTrackCollection;
use EugeneErg\Graph\New\Animations\Collections\ColorTrackCollection;
use EugeneErg\Graph\New\Animations\Segments\ColorSegment;
use EugeneErg\Graph\New\Animations\Tracks\ColorTrack;

class BrushObjectsEffect implements EffectInterface
{
    public function __construct(
        public readonly string $color,
        public readonly int $stepMilliSeconds = 0,
    ) {
    }

    /**
     * @param ColorTrackCollection $tracks
     * @param int|null $startMilliSeconds
     * @return int
     */
    public function apply(AbstractTrackCollection $tracks, ?int $startMilliSeconds = null): int
    {
        $startMilliSeconds = $startMilliSeconds ?? $tracks->reduce(
            fn (int $result, ColorTrack $track): int => max($result, $track->getDurationMilliSecond()),
            0,
        );

        /** @var ColorTrack $track */
        foreach ($tracks as $track) {
            $track->addSegment(
                new ColorSegment($this->stepMilliSeconds, $this->color),
                $startMilliSeconds,
            );
            $startMilliSeconds += $this->stepMilliSeconds;
        }

        return $startMilliSeconds + $tracks->count() * $this->stepMilliSeconds;
    }
}
