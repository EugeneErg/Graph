<?php declare(strict_types = 1);
namespace EugeneErg\Graph\Services;

use EugeneErg\Graph\ValueObjects\Angle;
use EugeneErg\Graph\ValueObjects\Collections\CustomCollection;
use EugeneErg\Graph\ValueObjects\Collections\ScalarCollection;
use EugeneErg\Graph\ValueObjects\Line;
use EugeneErg\Graph\ValueObjects\Line2D;
use EugeneErg\Graph\ValueObjects\Point2D;
use EugeneErg\Graph\ValueObjects\Polygon;
use EugeneErg\Graph\ValueObjects\Ray2D;

class VisibilityPolygonService extends AbstractService
{
    public function getVisibilityPolygon(Polygon $polygon, Point2D ...$visors): Polygon
    {
        $polygons = CustomCollection::map(
            static function ($visor) use ($polygon): Polygon {
                return $this->getVisibilityPolygonOnce($polygon, $visor);
            },
            new CustomCollection($visors)
        );

        return Polygon::intersect(...$polygons);
    }

    private function getVisibilityPolygonOnce(Polygon $polygon, Point2D $visor): Polygon
    {
        $polygons = CustomCollection::map(
            static function (int $key) use ($polygon, $visor): Polygon {
                return $this->getVisibilityPolygonSingle($polygon, $key);
            },
            ScalarCollection::keys($polygon, $visor, true)
        );

        return Polygon::merge(...$polygons);
    }

    public function getVisibilityPolygonSingle(Polygon $polygon, int $position): Polygon
    {
        $visor = $polygon[$position++];
        $previous = $polygon[$position++];
        $firstAngle = $previousAngle = $visor->getAngle($previous)->modulo();
        $visibleAngle = null;
        $totalAngle = $zeroAngle = new Angle();
        $piAngle = Angle::pi();
        $result = [
            new Ray2D($visor, $zeroAngle, .0),
            new Ray2D($previous, $zeroAngle, $visor->getDistance($previous)),
        ];
        $clockwise = true;
        $resultPosition = 1;
        $intersectCount = 0;
        $intersectIncClockwiseDirection = true;
        $isDistancing = true;
        $outOfSight = false;
        $finalDecrementIsDistancing = null;

        for (
            $current = $polygon[$position++];
            $current !== $visor;
            $current = $polygon[$position++]
        ) {
            $currentAngle = $visor->getAngle($current)->modulo();//если нулл, считаем его видным, если конечно сейчас обход не с противоположной стороны
            $delta = $currentAngle->minus($previousAngle)->modulo();

            if ($delta > $piAngle) {
                $delta = $delta->minus(Angle::pi(2));
            }

            $newTotalAngle = $totalAngle->plus($delta);
            $changedDirection = ($newTotalAngle > $totalAngle) !== $clockwise;
            $clockwise = $changedDirection !== $clockwise;
            $isIntersect = $outOfSight
                && $visibleAngle->between($currentAngle, $previousAngle, Angle::BETWEEN_FLAG_IS_SECTION);
            $isDistancing = $changedDirection ? $this->isDistancing(
                $polygon[$position - 3],
                $previous,
                $current,
                $clockwise
            ) : $isDistancing;
            $totalAngle = $newTotalAngle;

            if ($isIntersect) {
                $intersectIncClockwiseDirection === $clockwise ? $intersectCount++ : $intersectCount--;

                if ($intersectCount !== 0 || $finalDecrementIsDistancing !== $isDistancing) {// || $isDistancing !== $finalDecrementIsDistancing) {
                    $previousAngle = $currentAngle;
                    $previous = $current;

                    continue;
                }

                $outOfSight = false;
                $isDistancing = false;//or !$isDistancing ?
                $changedDirection = true;
            }

            if ($outOfSight) {
                $previousAngle = $currentAngle;
                $previous = $current;

                continue;
            }

            $fullCircle = $totalAngle->absolute() > Angle::pi(2);

            if ($isDistancing && ($changedDirection || $fullCircle)) {
                //повернули и скрылись - новые вершины не добавляяются, пока не вернемся
                $visibleAngle = $fullCircle ? $firstAngle : $previousAngle;
                $intersectCount = 1;
                $intersectIncClockwiseDirection = $clockwise;
                $finalDecrementIsDistancing = !$fullCircle;
                $outOfSight = true;
                $previousAngle = $currentAngle;
                $previous = $current;

                continue;
            }

            $currentRay = new Ray2D($current, $totalAngle, $visor->getDistance($current));

            if ($clockwise) {
                if ($changedDirection && !$isDistancing && !$isIntersect) {
                    array_splice($result, $resultPosition + 1);
                }

                $resultPosition = count($result);
                $result[] = $currentRay;
            } else {
                for (
                    $newResultPosition = $resultPosition;
                    $newResultPosition > 0;
                    $newResultPosition--
                ) {
                    $isDistancing = $this
                        ->isDistancingRay($result[$newResultPosition - 1], $result[$resultPosition], $currentRay);

                    if ($isDistancing !== false) {
                        break;
                    }
                }

                if ($isDistancing === true) {
                    $visibleAngle = $result[$newResultPosition - 1]->getAngle()
                        ->minus($totalAngle)->plus($currentAngle)->modulo();
                    $outOfSight = true;
                    $intersectCount = 1;
                    $finalDecrementIsDistancing = false;
                }

                $rays = [$currentRay];

                if (!$isDistancing && isset($result[$newResultPosition + 1])) {
                    $partPoint = (new Line2D($result[$newResultPosition]->getPoint(), $result[$newResultPosition + 1]
                        ->getPoint()))
                        ->intersection(new Line2D($visor, $current));

                    if ($partPoint !== null) {
                        $rays[] = new Ray2D($partPoint, $currentAngle, $visor->getDistance($partPoint));
                    }
                }

                array_splice(
                    $result,
                    $newResultPosition,
                    $resultPosition - $newResultPosition + (int) $changedDirection,
                    $isDistancing ? [] : $rays
                );
                $resultPosition = $newResultPosition - (int) $isDistancing;
            }

            $previousAngle = $currentAngle;
            $previous = $current;
        }

        $newResultPosition = $this
            ->findNewPosition($resultPosition + 1, $result, $totalAngle);
        array_splice($result, $newResultPosition);

        return Polygon::map(static function (Ray2D $ray): Point2D {
            return $ray->getPoint();
        }, new CustomCollection($result));
    }

    private function isDistancing(// отдаляемся ли? если мы тут, значит направление поменялось
        Point2D $prePrevious,
        Point2D $previous,
        Point2D $current,
        bool $clockwise
    ): bool {
        $newPointOnRight = $previous->getAngle($current)
            ->minus($prePrevious->getAngle($previous))
            ->modulo() < Angle::pi();

        return $newPointOnRight === $clockwise;
    }

    /**
     * @param int $position
     * @param Ray2D[] $result
     * @param Angle $findAngle
     * @return int
     */
    private function findNewPosition(int $position, array $result, Angle $findAngle): int
    {
        for (
            $position--;
            $position >= 0;
            $position--
        ) {
            if ($result[$position]->getAngle()->isEqual($findAngle)) {
                return $position + 1;
            }

            if ($result[$position]->getAngle() < $findAngle) {
                return $position + 1;
            }
        }

        return $position;
    }

    private function isDistancingRay(Ray2D $current, Ray2D $from, Ray2D $to): ?bool
    {
        if ($current->getAngle()->isEqual($from->getAngle()) && $current->getLength() > $from->getLength()) {
            return false;
        }

        if ($current->getAngle() < $to->getAngle() || $current->getAngle() > $from->getAngle()) {
            return null;
        }

        if ($current->getAngle()->isEqual($to->getAngle()) && $current->getLength() > $to->getLength()) {
            return null;
        }

        if ($from->getAngle()->isEqual($to->getAngle())) {
            return min($from->getLength(), $to->getAngle()) > $current->getLength();
        }

        return $current->getAngle()
            ->minus($to->getAngle())
            ->divided($from->getAngle()->minus($to->getAngle())->getRadian())->getRadian()
        * ($from->getLength() - $to->getLength()) > $current->getLength() - $to->getLength();
    }
}
