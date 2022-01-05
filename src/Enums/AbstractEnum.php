<?php declare(strict_types=1);
namespace EugeneErg\Graph\Enums;

use Error;
use JsonSerializable;

abstract class AbstractEnum implements JsonSerializable
{
    protected static array $values;

    private $value;

    final public function __construct($value)
    {
        $this->value = $value;
    }

    public static function __callStatic(string $name, array $arguments): self
    {
        if (!isset(static::$values[$name])) {
            throw new Error(vsprintf('Call to undefined method %s::%s()', [static::class, $name]));
        }

        return new static(static::$values[$name]);
    }

    public function getValue()
    {
        return $this->value;
    }

    public function isEqual(AbstractEnum $enum): bool
    {
        return $this->value === $enum->value;
    }

    public function jsonSerialize()
    {
        return $this->getValue();
    }

    public function __toString(): string
    {
        return (string) $this->getValue();
    }
}
