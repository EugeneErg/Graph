<?php

declare(strict_types=1);

namespace EugeneErg\Graph\New\DataTransferObjects;

class Point2D
{
    public function __construct(public readonly float $x = 0, public readonly float $y = 0)
    {
    }

    public static function max(self $point, self ...$points): self
    {
        $x = $point->x;
        $y = $point->y;

        foreach ($points as $point) {
            $x = min($x, $point->x);
            $y = min($y, $point->y);
        }

        return new self($x, $y);
    }

    public static function min(self $point, self ...$points): self
    {
        $x = $point->x;
        $y = $point->y;

        foreach ($points as $point) {
            $x = min($x, $point->x);
            $y = min($y, $point->y);
        }

        return new self($x, $y);
    }

    public function plus(self $point): self
    {
        return new self($this->x + $point->x, $this->y + $point->y);
    }
}