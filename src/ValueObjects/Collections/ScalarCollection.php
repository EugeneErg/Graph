<?php namespace EugeneErg\Graph\ValueObjects\Collections;

class ScalarCollection extends AbstractCollection
{
    public static function flip(ScalarCollection $collection): self
    {
        return static::create(array_flip($collection->records));
    }

    public static function keys(AbstractCollection $collection, $searchValue = null, bool $strict = false): self
    {
        return static::create(func_num_args() === 1
            ? array_keys($collection->records)
            : array_keys($collection->records, $searchValue, $strict)
        );
    }

    public static function randomKeys(AbstractCollection $collection, int $num): self
    {
        if ($num > $collection->count) {
            throw new \Exception('Second argument has to be between 1 and the number of elements in the array');
        }

        return static::create((array) array_rand($collection->records, $num));
    }
}
