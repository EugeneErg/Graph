<?php declare(strict_types = 1);
namespace EugeneErg\Graph\ValueObjects\Collections;

class NumericCollection extends ScalarCollection
{
    protected const COMPARE_FLAGS = self::COMPARE_FLAG_BY_VALUE;

    public static function range($start, $end, $step = 1): self
    {
        return static::create(range($start, $end, $step));
    }

    public function getProduct()
    {
        return array_product($this->records);
    }

    public function getSum()
    {
        return array_sum($this->records);
    }

    public function max()
    {
        return $this->count ? max(...$this->records) : 0;
    }

    public function min()
    {
        return $this->count ? min(...$this->records) : 0;
    }
}