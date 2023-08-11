<?php

declare(strict_types=1);

namespace EugeneErg\Graph\New\Animations\DataTransferObjects;

use EugeneErg\Graph\New\Animations\Tracks\ColorTrack;
use EugeneErg\Graph\New\Animations\Tracks\Point2DTrack;
use EugeneErg\Graph\New\Animations\Tracks\IntegerTrack;

class Circle implements DataTransferObjectInterface
{
    public function __construct(
        public readonly string $text,
        public readonly IntegerTrack $radius,
        public readonly ColorTrack $color,
        public readonly Point2DTrack $center,
        public readonly IntegerTrack $opacity,
    ) {
    }
}
