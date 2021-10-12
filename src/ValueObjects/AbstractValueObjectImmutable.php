<?php namespace EugeneErg\Graphs\ValueObjects;

use Closure;
USE JsonSerializable;

abstract class AbstractValueObjectImmutable implements JsonSerializable
{
    use ValueObjectTrait {
        __call as private setAttributes;
    }

    public function __call(string $name, array $arguments): self
    {
        $result = clone $this;
        $result->setAttributes($name, $arguments);
        return $result;
    }
}
