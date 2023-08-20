<?php

declare(strict_types=1);

namespace EugeneErg\Graph\New\Services;

use EugeneErg\Collections\IntegerCollection;
use EugeneErg\Graph\New\Collections\AngleCollection;
use EugeneErg\Graph\New\Collections\Point2DCollection;
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

    public static function insertCircles(
        IntegerCollection $radii,
        ?Angle $startAngle = null,
        ?Point2D $center = null,
    ): Point2DCollection {
        if ($radii->count() === 0) {
            return new Point2DCollection();
        }

        if ($radii->count() === 1) {
            return new Point2DCollection([$center]);
        }

        $pi = Angle::pi();

        if ($radii->count() === 2) {
            $resultRadius = $radii->reduce(fn (int $current, int $next): int => $current + $next, 0);
            $angles = new AngleCollection([Angle::pi(1 / 2), Angle::pi(1 / 2)]);
            $scale = 1;
        //todo } elseif ($radii->count() === 3) {
        } else {
            $maxRadius = $radii->reduce(fn (int $current, int $next): int => max($current, $next), 0);
            $left = 2 * $maxRadius;
            $right = $maxRadius + $maxRadius / sin(M_PI / $radii->count());

            do {
                $resultRadius = ($left + $right) / 2;
                $angles = AngleCollection::fromMap(
                    fn(int $radius): Angle => Angle::asin($radius / ($resultRadius - $radius)),
                    $radii,
                );
                /** @var Angle $radian */
                $radian = $angles->reduce(
                    fn(Angle $result, Angle $next): Angle => $result->plus($next),
                    new Angle(),
                );
                $radian->greaterThan($pi) ? $right = $resultRadius : $left = $resultRadius;
            } while (!$radian->isEqual($pi, 0.001) && abs($resultRadius - $left) >= 0.001);

            $maxAngle = Angle::max(...$angles);

            if (Angle::pi(2)->greaterThanOrEqual($maxAngle->times($radii->count()))) {
                $angles = AngleCollection::fromFill(0, $radii->count(), Angle::pi(2 / $radii->count()));
                $scale = 1;
            } else {
                $scale = $pi->getRadian() / $radian->getRadian();
            }
        }

        $startAngle = $startAngle ?? new Angle();

        return Point2DCollection::fromMap(
            function (int $radius, Angle $angle) use ($resultRadius, &$startAngle, $center, $scale): Point2D {
                if ($startAngle->getRadian() != 0) {
                    $startAngle = $startAngle->plus($angle->times($scale));
                }

                $result = self::getPoint((int) ceil($resultRadius - $radius), $startAngle, $center);
                $startAngle = $startAngle->plus($angle->times($scale));

                return $result;
            },
            $radii,
            $angles,
        );
    }
}
