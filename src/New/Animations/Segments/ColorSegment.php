<?php

declare(strict_types=1);

namespace EugeneErg\Graph\New\Animations\Segments;

class ColorSegment implements SegmentInterface
{
    public function __construct(
        private readonly int $durationMilliSecond,
        private readonly string $value,
    ) {
    }

    public function getDurationMilliSecond(): int
    {
        return $this->durationMilliSecond;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}