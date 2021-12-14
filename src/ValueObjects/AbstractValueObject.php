<?php declare(strict_types = 1);
namespace EugeneErg\Graph\ValueObjects;

use EugeneErg\Graph\Traits\AttributeTrait;
use JsonSerializable;

abstract class AbstractValueObject implements JsonSerializable
{
    use AttributeTrait;

    public function toArray(): array
    {
        return [];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function __debugInfo(): array
    {
        return $this->toArray();
    }
}
