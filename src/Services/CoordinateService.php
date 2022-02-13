<?php declare(strict_types = 1);
namespace EugeneErg\Graph\Services;

use EugeneErg\Graph\Dto\Point2D;
use EugeneErg\Graph\Processes\Collections\Point2DGraphCollection;
use EugeneErg\Graph\ValueObjects\Angle;

class CoordinateService extends AbstractService
{
    public function getAngle(int $count, ?Angle $angleSize = null): Angle
    {
        return ($angleSize ?? Angle::pi(2))->divided($count);
    }

    public function getFinalAngle(Angle $angle, int $number, ?Angle $startAngle = null): Angle
    {
        return $startAngle === null ? $angle->times($number) : $angle->times($number)->plus($startAngle);
    }

    public function findAnOccupiedAngle(int $radiusA, int $radiusB): Angle
    {
        return Angle::asin($radiusB / ($radiusA + $radiusB))->times(2);
    }

    public function getPoint(int $distance, Angle $angle, ?Point2D $center = null): Point2D
    {
        return new Point2D(
            ($center === null ? 0 : $center->getX()) + $distance * $angle->sin(),
            ($center === null ? 0 : $center->getY()) + $distance * -$angle->cos()
        );
    }

    public function getPoints(
        int $count,
        int $distance,
        ?Point2D $center = null,
        ?Angle $startAngle = null,
        ?Angle $occupiedAngle = null
    ): Point2DGraphCollection {
        $center = $center ?? new Point2D();
        $result = new Point2DGraphCollection();

        switch ($count) {
            case 1: $result[] = $center;
            case 0: return $result;
        }

        $delta = ($occupiedAngle ?? Angle::pi(2))->divided($count);
        $startAngle = $startAngle ?? new Angle();

        for ($number = 0; $number < $count; $number++) {
            $angle = $delta->times($number)->plus($startAngle);
            $result[] = new Point2D(
                round($center->getX() + $distance * $angle->sin(), 2),
                round($center->getY() + $distance * -$angle->cos(), 2)
            );
        }

        return $result;
    }

    public function getRadius(int $subRadius, int $count): int
    {
        return (int) ceil($subRadius / sin(pi() / $count));
    }
}
