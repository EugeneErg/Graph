<?php declare(strict_types = 1);
namespace EugeneErg\Graph\ValueObjects;

class Point2D extends Point
{
    public const AXIS_X = 0;
    public const AXIS_Y = 1;

    public function __construct(float $x, float $y)
    {
        parent::__construct($x, $y);
    }

    public function getX(): float
    {
        return $this->getAxis(self::AXIS_X);
    }

    public function getY(): float
    {
        return $this->getAxis(self::AXIS_Y);
    }

    public function setX(float $value): void
    {
        $this->setAxis(self::AXIS_X, $value);
    }

    public function setY(float $value): void
    {
        $this->setAxis(self::AXIS_Y, $value);
    }

    /**
     * @param Point2D $point
     * @return Angle|null
     */
    public function getAngle(Point $point): ?Angle
    {
        if ($this->isEqual($point)) {
            return null;
        }

        $point = $point->minus($this);

        return new Angle(
            $point->getY() === .0
                ? pi() / ($point->getX() < 0 ? -2 : 2)
                : atan($point->getX() / $point->getY()) + ($point->getY() < 0 ? pi() : 0)
        );
    }
}
