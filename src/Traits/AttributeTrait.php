<?php declare(strict_types=1);
namespace EugeneErg\Graph\Traits;

use InvalidArgumentException;

trait AttributeTrait
{
    public function __get(string $name)
    {
        $method = 'get' . ucfirst($name);

        if (method_exists($this, $method)) {
            return $this->$method();
        }

        throw new InvalidArgumentException('Cannot get property ' . $name);
    }

    public function __set(string $name, $value): void
    {
        $method = 'set' . ucfirst($name);

        if (method_exists($this, $method)) {
            $this->$method($value);
        }

        throw new InvalidArgumentException('Cannot set property ' . $name);
    }

    public function __isset(string $name): bool
    {
        $method = 'has' . ucfirst($name);

        if (method_exists($this, $method)) {
            return $this->$method();
        }

        $method = 'get' . ucfirst($name);

        if (method_exists($this, $method)) {
            return $this->$method() !== null;
        }

        return false;
    }
}
