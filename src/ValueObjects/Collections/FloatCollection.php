<?php declare(strict_types = 1);
namespace EugeneErg\Graph\ValueObjects\Collections;

/**
 * @see FloatCollection::getProduct()
 * @property-read float $product
 * @see FloatCollection::getSum()
 * @property-read float $sum
 */
class FloatCollection extends NumericCollection
{
    /**
     * @param float $start
     * @param float $end
     * @param float $step
     * @return NumericCollection
     */
    public static function range($start, $end, $step = 1.): NumericCollection
    {
        return parent::range((float) $start, (float) $end, (float) $step);
    }

    public function getProduct(): float
    {
        return (float) parent::getProduct();
    }

    public function getSum(): float
    {
        return (float) parent::getSum();
    }

    public function max(): float
    {
        return (float) parent::max();
    }

    public function min(): float
    {
        return (float) parent::min();
    }
}