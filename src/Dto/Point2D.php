<?php declare(strict_types=1);
namespace EugeneErg\Graph\Dto;

class Point2D
{
    private float $x;
    private float $y;

    public function __construct(float $x = 0, float $y = 0)
    {
        $this->x = $x;
        $this->y = $y;
    }

    public static function max(Point2D $point, Point2D ...$points): self
    {
        $result = clone $point;

        foreach ($points as $point) {
            $result->x = max($result->x, $point->x);
            $result->y = max($result->y, $point->y);
        }

        return $result;
    }

    public static function min(Point2D $point, Point2D ...$points): self
    {
        $result = clone $point;

        foreach ($points as $point) {
            $result->x = min($result->x, $point->x);
            $result->y = min($result->y, $point->y);
        }

        return $result;
    }

    public function getX(): float
    {
        return $this->x;
    }

    public function getY(): float
    {
        return $this->y;
    }
}
