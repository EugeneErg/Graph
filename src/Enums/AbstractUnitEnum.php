<?php declare(strict_types=1);

namespace EugeneErg\Graph\Enums;

/**
 * @property-read string $name
 */
abstract class AbstractUnitEnum
{
    private string $name;
    protected static array $cases = [];
    private static ?array $instances = null;

    /** @return static[] */
    final public static function cases(): array
    {
        return array_values(static::getInstances());
    }

    private static function getInstance(string $name): ?self
    {
        $instances = static::getInstances();

        return $instances[$name] ?? null;
    }

    /** @return static[] */
    private static function getInstances(): array
    {
        if (static::$instances === null) {
            foreach (static::$cases as $name) {
                static::$instances[$name] = static::createInstance($name);
            }
        }

        return static::$instances;
    }

    protected static function createInstance(string $name): self
    {
        /** @var self $result */
        $result = new static();
        $result->name = $name;

        return $result;
    }

    public function __get(string $name)
    {
        if ($name === 'name') {
            return $this->name;
        }
    }

    /** @return static|mixed */
    public static function __callStatic(string $name, array $arguments)
    {
        $result = static::getInstance($name);

        if ($result === null) {
            $class = static::class;

            throw new \Error("Undefined property: {$class}::\${$name}");
        }

        return $result;
    }

    final public function __set(string $name, $value)
    {
        $class = static::class;

        throw new \Error("Cannot create dynamic property {$class}::\${$name}");
    }

    final private function __construct() {}
    final private function __destruct() {}
    final private function __clone() {}
    final private function __sleep() {}
    final private function __wakeup() {}
    final private function __set_state() {}
}
