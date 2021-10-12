<?php namespace EugeneErg\Graphs\ValueObjects\Temp;

abstract class AbstractTempDto
{
    public function __set(string $name, $value): void
    {
        throw new \Exception();
    }

    public function __debugInfo()
    {
        return (array) $this;
    }

    public function __get(string $name)
    {
        throw new \Exception();
    }
}