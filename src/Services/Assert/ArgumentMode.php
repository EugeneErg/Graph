<?php declare(strict_types=1);

namespace EugeneErg\Graph\Services\Assert;

class ArgumentMode implements ArgumentModeInterface
{
    /** @var callable */
    private $valueCallback;
    /** @var callable */
    private $prefixCallback;

    public function __construct(callable $valueCallback, callable $prefixCallback)
    {
        $this->valueCallback = $valueCallback;
        $this->prefixCallback = $prefixCallback;
    }

    public function getValue($value)
    {
        ($this->valueCallback)($value);
    }

    public function getPrefix($value): string
    {
        ($this->prefixCallback)($value);
    }
}
