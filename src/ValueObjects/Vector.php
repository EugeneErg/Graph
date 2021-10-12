<?php namespace EugeneErg\Graphs\ValueObjects;

use EugeneErg\Graphs\ValueObjects\Collections\AbstractCollection;
use EugeneErg\Graphs\ValueObjects\Collections\FloatCollection;

/**
 * @method bool offsetExists(int $offset)
 */
class Vector extends FloatCollection
{
    public const COMPARE_FLAGS = self::COMPARE_FLAG_BY_VALUE | self::COMPARE_FLAG_BY_KEY_AS_STRING;

    public function __construct(float ...$values)
    {
        parent::__construct($values);
    }

    /**
     * @param int $offset
     * @return float
     */
    public function offsetGet($offset): float
    {
        return parent::offsetGet($offset);
    }

    /**
     * @param int $offset
     * @param float $value
     * @throws \Exception
     */
    public function offsetSet($offset, $value): void
    {
        if (!isset($this[$offset])) {
            throw new \Exception('impossible to resize vector');
        }

        $this->records[$offset] = $value;
    }

    public function product(float $value): Vector
    {
        return static::map(static function (float $valueA) use ($value): float {
            return $valueA * $value;
        }, $this);
    }

    public function productVector(Vector $vector): float
    {
        return static::map(static function (float $valueA, float $valueB): float {
            return $valueA * $valueB;
        }, $this, $vector)->sum;
    }

    public function minus(Vector $vector): Vector
    {
        return static::map(static function (float $valueA, float $valueB): float {
            return $valueA - $valueB;
        }, $this, $vector);
    }

    protected static function create(array $records = []): AbstractCollection
    {
        return new static(...$records);
    }
}