<?php declare(strict_types = 1);
namespace EugeneErg\Graph\ValueObjects;

use EugeneErg\Graph\ValueObjects\Collections\IntCollection;

/**
 * @see Line::getPointA()
 * @property-read Point $pointA
 * @see Line::getPointB()
 * @property-read Point $pointB
 */
class Line extends Matrix
{
    public const POINT_A = 0;
    public const POINT_B = 1;

    public function __construct(Point $pointA, Point $pointB)
    {
        parent::__construct($pointA, $pointB);
    }

    public function getPointA(): Point
    {
        /** @var Point $result */
        $result = $this[self::POINT_A];

        return $result;
    }

    public function getPointB(): Point
    {
        /** @var Point $result */
        $result = $this[self::POINT_B];

        return $result;
    }

    public function getDimension(): int
    {
        return $this->getColCount();
    }

    public function intersection(Line $line): ?Point
    {
        $pointA = $this->pointB->minus($this->pointA);
        $pointB = $line->pointB->minus($line->pointA);
        $matrix = Matrix::createByVectorProduct($pointA, $pointB);
        $deltas = Matrix::createMirror(static function (int $axisA, int $axisB) use ($matrix): float {
            return $matrix[$axisA][$axisB] - $matrix[$axisB][$axisA];
        }, $this->getDimension());
        $maxDeltas = IntCollection::map(static function (Vector $delta): int {
            return $delta->reduce(static function (?int $maxAxis, float $value, int $axis) use ($delta): int {
                return $maxAxis === null || $value > $delta[$maxAxis] ? $axis : $maxAxis;
            });
        }, $deltas);

        $point = $this->pointA->foreach(function ($axisB, $axisA) use ($deltas, $pointB, $pointA, $line): float {
            return ($pointB[$axisA] * (
                    $this->pointA[$axisA] * $this->pointB[$axisB] - $this->pointA[$axisB] * $this->pointB[$axisA]
                ) + $pointA[$axisA] * (
                    $line->pointA[$axisA] * $line->pointB[$axisB] - $line->pointA[$axisB] * $line->pointB[$axisA]
                )) / $deltas[$axisA][$axisB];
        }, $maxDeltas);

        foreach ($point as $axis => $value) {
            $valueA = $this->pointA[$axis];
            $valueB = $this->pointB[$axis];

            if ($value > max($valueA, $valueB) || $value < min($valueA, $valueB)) {
                return null;
            }
        }

        return $point;
    }
}