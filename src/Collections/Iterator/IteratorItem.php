<?php declare(strict_types=1);

namespace EugeneErg\Graph\Collections\Iterator;

use EugeneErg\Graph\ValueObjects\AbstractValueObject;

/**
 * @property-read string $key
 * @property-read mixed $value
 */
class IteratorItem extends AbstractValueObject
{
    private $key;
    private $value;

    public function __construct(string $key, $value)
    {
        $this->key = $key;
        $this->value = $value;
    }

    /** @return mixed */
    public function getValue()
    {
        return $this->value;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function toArray(): array
    {
        return [$this->key => $this->value];
    }

    public function __toString(): string
    {
        return $this->key;
    }

    public function __debugInfo(): array
    {
        return [
            'key' => $this->key,
            'value' => $this->value,
        ];
    }
}
