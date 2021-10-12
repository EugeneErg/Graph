<?php namespace EugeneErg\Graphs\ValueObjects;

use EugeneErg\Graphs\ValueObjects\Collections\AbstractCollection;

/**
 * @method Point2D current()
 */
class Polygon extends AbstractCollection
{
    public function __construct(Point2D ...$points)
    {
        parent::__construct($points);
    }

    /**
     * @param self ...$arrays
     * @return $this
     */
    public static function merge(AbstractCollection ...$arrays): AbstractCollection
    {

    }

    /**
     * @param self ...$arrays
     * @return $this
     */
    public static function intersect(AbstractCollection ...$arrays): AbstractCollection
    {

    }

    public function offsetExists($offset): bool
    {
        return true;
    }

    public function offsetGet($offset): Point2D
    {
        return parent::offsetGet($this->getKeyOfNumber($offset));
    }

    public function getKeyOfNumber(int $number): int
    {
        $count = $this->count;

        return ($number < 0 && $number !== - $count ? $count : 0) + ($number % $count);
    }

    public function next(): Point2D
    {
        return parent::next() ?? $this->rewind();
    }

    protected static function create(array $records = []): AbstractCollection
    {
        return new static(...$records);
    }
}
