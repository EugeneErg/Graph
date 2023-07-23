<?php

declare(strict_types=1);

namespace EugeneErg\Graph\New\Animations\Segments;

class RadiusSegment implements SegmentInterface
{
    public function __construct(
        private readonly int $durationMilliSecond,
        private readonly int $value,
    ) {
    }

    public function getDurationMilliSecond(): int
    {
        return $this->durationMilliSecond;
    }

    public function getValue(): int
    {
        return $this->value;
    }
}