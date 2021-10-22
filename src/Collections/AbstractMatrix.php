<?php declare(strict_types=1);
namespace EugeneErg\Graph\Collections;

abstract class AbstractMatrix extends AbstractCollection
{
    /** @inheritDoc */
    public function offsetGet($offset)
    {
        if (!static::isValidKey($offset) || $this->offsetExists($offset)) {
            return parent::offsetGet($offset);
        }

        $result = $this->createEmptyElement($offset);
        $this->offsetSet($offset, $result);

        return $result;
    }

    abstract protected function createEmptyElement($key);
}
