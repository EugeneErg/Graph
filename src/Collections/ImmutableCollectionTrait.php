<?php declare(strict_types = 1);
namespace EugeneErg\Graph\Collections;

use Error;

trait ImmutableCollectionTrait
{
    public function offsetSet($offset, $value): void
    {
        throw new Error(sprintf('Cannot use object of type %s as array', static::class));
    }

    public function offsetUnset($offset): void
    {
        throw new Error(sprintf('Cannot use object of type %s as array', static::class));
    }
}
