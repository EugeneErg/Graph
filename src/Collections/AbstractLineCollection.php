<?php declare(strict_types=1);

namespace EugeneErg\Graph\Collections;

abstract class AbstractLineCollection extends AbstractCollection2 implements \ArrayAccess
{
    public function offsetExists($offset): bool
    {
        return $this->isset($offset);
    }

    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value): void
    {
        $this->set($offset, $value);
    }

    public function offsetUnset($offset)
    {
        $this->unset($offset);
    }
}
