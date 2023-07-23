<?php

declare(strict_types=1);

namespace EugeneErg\Graph\New\Animations\DataTransferObjects;

use EugeneErg\Graph\New\Animations\Tracks\ColorTrack;
use EugeneErg\Graph\New\Animations\Tracks\Point2DTrack;

class Line implements DataTransferObjectInterface
{
    public function __construct(
        public readonly ColorTrack $color,
        public readonly Point2DTrack $from,
        public readonly Point2DTrack $to,
    ) {
    }
}