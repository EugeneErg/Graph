<?php namespace EugeneErg\Graph\ValueObjects;

use Exception;
use ReflectionClass;
use ReflectionMethod;
use stdClass;

trait ValueObjectTrait
{
    private $attributes;
    private static $defaults = [];
    private static $getters = [];

    public function __construct(...$arguments)
    {
        $this->setVariadicAndDefault();
        $default = self::$defaults[static::class];
        $number = 0;
        $count = count($arguments);
        $this->attributes = new stdClass();

        foreach ($default as $name => $value) {
            $this->attributes->$name = $number >= $count ? $value : $arguments[$number++];
        }
    }

    public function __get(string $name)
    {
        if (isset(self::$getters[static::class][$name])) {
            $methodName = self::$getters[static::class][$name];

            return $this->$methodName($this->attributes);
        }

        if (!isset($this->attributes->$name) && !property_exists($this->attributes, $name)) {
            throw new Exception('Class ' . static::class . ' not have property ' . $name);
        }

        return $this->attributes->$name;
    }

    private function setVariadicAndDefault(): void
    {
        if (isset(self::$defaults[static::class])) {
            return;
        }

        self::$defaults[static::class] = [];
        $parameters = (new ReflectionMethod($this, '__construct'))->getParameters();

        foreach ($parameters as $parameter) {
            self::$defaults[static::class][$parameter->getName()] =
                $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() :
                    ($parameter->isVariadic() ? [] : null);
        }

        $reflectionClass = new ReflectionClass(static::class);

        foreach ($reflectionClass->getMethods() as $method) {
            if (preg_match('/^get([A-Z].*)Attribute$/', $method->getName(), $matches)) {
                self::$getters[static::class][lcfirst($matches[1])] = $method->getName();
            }
        }
    }

    public function __call(string $name, array $arguments)
    {
        $methodName = $name . 'Attribute';

        if (!method_exists($this, $methodName)) {
            throw new Exception('cannot set read-only attribute ' . $name . 'in class ' . static::class);
        }

        return $this->$methodName($this->attributes, ...$arguments);
    }

    public function __isset(string $name): bool
    {
        if (isset(self::$getters[static::class][$name])) {
            $methodName = self::$getters[static::class][$name];

            return $this->$methodName($this->attributes) !== null;
        }

        return isset($this->attributes->$name);
    }

    public function toArray(): array
    {
        $result = (array) $this->attributes;

        /*foreach (self::$getters[static::class] as $name => $method) {
            if (!isset($result[$name]) && !array_key_exists($name, $result)) {
                $result[$name] = $this->$method($this->attributes);
            }
        }*/

        return $result;
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function __debugInfo(): array
    {
        return $this->toArray();
    }

    /** @deprecated */
    public function __toString(): string
    {
        return spl_object_hash($this);
    }

    public function __clone()
    {
        $this->attributes = clone $this->attributes;
    }

    private function getAttributes(): stdClass
    {
        return $this->attributes;
    }

    public static function __set_state(array $attributes): self
    {
        $reflectionClass = new ReflectionClass(static::class);
        /** @var static $result */
        $result = $reflectionClass->newInstanceWithoutConstructor();
        $result->attributes = (object) $attributes;

        return $result;
    }

    public function __serialize(): array
    {
        return (array) $this->attributes;
    }

    public function __unSerialize(array $attributes): void
    {
        $this->attributes = (object) $attributes;
    }
}