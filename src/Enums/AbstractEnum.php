<?php declare(strict_types=1);
namespace EugeneErg\Graph\Enums;

use Error;

abstract class AbstractEnum
{
    /** @var array */
    protected static $values;

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
}
