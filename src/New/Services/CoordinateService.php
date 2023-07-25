<?php

declare(strict_types=1);

namespace EugeneErg\Graph\New\Services;

use EugeneErg\Graph\New\DataTransferObjects\Point2D;
use EugeneErg\Graph\New\ValueObjects\Angle;

class CoordinateService
{
    public static function getAngle(int $count, ?Angle $angleSize = null): Angle
    {
        return ($angleSize ?? Angle::pi(2))->divided($count);
    }

    public static function getRadius(int $subRadius, int $count): int
    {
        return (int) ceil($subRadius / sin(pi() / $count));
    }

    public static function getFinalAngle(Angle $angle, int $number, ?Angle $startAngle = null): Angle
    {
        return $startAngle === null ? $angle->times($number) : $angle->times($number)->plus($startAngle);
    }

    public static function getPoint(int $distance, Angle $angle, ?Point2D $center = null): Point2D
    {
        return new Point2D(
            ($center === null ? 0 : $center->x) + $distance * $angle->sin(),
            ($center === null ? 0 : $center->y) + $distance * -$angle->cos(),
        );
    }

    public static function findAnOccupiedAngle(int $radiusA, int $radiusB): Angle
    {
        return Angle::asin($radiusB / ($radiusA + $radiusB))->times(2);
    }
}
