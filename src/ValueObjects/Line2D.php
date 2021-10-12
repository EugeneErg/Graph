<?php namespace EugeneErg\Graphs\ValueObjects;

class Line2D extends Line
{
    public function __construct(Point2D $pointA, Point2D $pointB)
    {
        parent::__construct($pointA, $pointB);
    }

    /**
     * @param Line2D $line
     * @return Point2D|null
     */
    public function intersection(Line $line): ?Point
    {
        return parent::intersection($line);
    }
}
