<?php namespace EugeneErg\Graph\ValueObjects;

class Point extends Vector
{
    public function getAxis(int $axis): float
    {
        return $this[$axis];
    }

    public function setAxis(int $axis, float $value): void
    {
        $this[$axis] = $value;
    }

    public function getAngle(Point $point): ?Angle
    {



    }

    public function getDistanceSquared(Point $point): float
    {
        $point = $this->minus($point);

        return $point->productVector($point);
    }

    public function getDistance(Point $point): float
    {
        return sqrt($this->getDistanceSquared($point));
    }
}
