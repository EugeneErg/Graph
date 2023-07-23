<?php

declare(strict_types=1);

namespace EugeneErg\Graph\New\Animations\Segments;

interface SegmentInterface
{
    public function getDurationMilliSecond(): int;
    public function getValue(): mixed;
}