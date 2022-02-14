<?php declare(strict_types=1);

namespace EugeneErg\Graph\Enums;

/**
 * @property-read mixed $value
 */
abstract class AbstractBackedEnum extends AbstractUnitEnum
{
    private $value;

    /** @param int|string $value */
    final public static function from($value): self
    {
        $result = static::tryFrom($value);

        if ($result === null) {
            $class = static::class;

            throw new \ValueError("\"{$value}\" is not a valid scalar value for enum \"{$class}\"");
        }

        return $result;
    }

    /** @param int|string $value */
    final public static function tryFrom($value): ?self
    {
        $name = array_search($value, static::$cases, true);

        return $name === null ? null : static::__callStatic($name, []);
    }

    final public function __get(string $name)
    {
        return $name === 'value' ? $this->value : parent::__get($name);
    }

    protected static function createInstance(string $name): self
    {
        /** @var self $result */
        $result = parent::createInstance($name);
        $result->value = static::$cases[$name];

        return $result;
    }

    public function jsonSerialize()
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}
