<?php

declare(strict_types=1);

namespace EugeneErg\Graph\New\Animations\Segments;

use EugeneErg\Graph\New\DataTransferObjects\Point2D;

class Point2DSegment implements SegmentInterface
{
    public function __construct(
        private readonly int $durationMilliSecond,
        private readonly Point2D $value,
    ) {
    }

    public function getDurationMilliSecond(): int
    {
        return $this->durationMilliSecond;
    }

    public function getValue(): Point2D
    {
        return $this->value;
    }
}