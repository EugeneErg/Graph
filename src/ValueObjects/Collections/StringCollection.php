<?php namespace EugeneErg\Graphs\ValueObjects\Collections;

/**
 * @see AbstractCollection::getFlip()
 * @property-read AbstractCollection $flip
 */
class StringCollection extends ScalarCollection
{
    protected const COMPARE_FLAGS = self::COMPARE_FLAG_BY_VALUE_AS_STRING;

    /**
     * @param string|int|float $start
     * @param string|int|float $end
     * @param int $step
     * @return static
     */
    public static function range(string $start, string $end, int $step = 1): self
    {
        return static::create(range($start, $end, $step));
    }
}