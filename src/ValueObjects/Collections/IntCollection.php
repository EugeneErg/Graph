<?php namespace EugeneErg\Graphs\ValueObjects\Collections;

/**
 * @see IntCollection::getProduct()
 * @property-read int $product
 * @see IntCollection::getSum()
 * @property-read int $sum
 */
class IntCollection extends NumericCollection
{
    /**
     * @param int $start
     * @param int $end
     * @param int $step
     * @return NumericCollection
     */
    public static function range($start, $end, $step = 1): NumericCollection
    {
        return parent::range((int) $start, (int) $end, (int) $step);
    }

    public function getProduct(): int
    {
        return (int) parent::getProduct();
    }

    public function getSum(): int
    {
        return (int) parent::getSum();
    }

    public function max(): int
    {
        return (int) parent::max();
    }

    public function min(): int
    {
        return (int) parent::min();
    }
}