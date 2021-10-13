<?php declare(strict_types = 1);
namespace EugeneErg\Graph\ValueObjects;

class Ray2D
{
    /** @var Point2D */
    private $point;
    /** @var Angle */
    private $angle;
    /** @var float */
    private $length;

    public function __construct(Point2D $point, Angle $angle, float $length)
    {
        $this->point = $point;
        $this->angle = $angle;
        $this->length = $length;
    }

    public function getAngle(): Angle
    {
        return $this->angle;
    }

    public function getPoint(): Point2D
    {
        return $this->point;
    }

    public function getLength(): float
    {
        return $this->length;
    }
}
