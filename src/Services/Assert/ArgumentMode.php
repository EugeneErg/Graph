<?php declare(strict_types=1);

namespace EugeneErg\Graph\Services\Assert;

class ArgumentMode implements ArgumentModeInterface
{
    /** @var callable */
    private $valueCallback;
    private string $prefix;

    public function __construct(callable $valueCallback, string $prefix)
    {
        $this->valueCallback = $valueCallback;
        $this->prefix = $prefix;
    }

    public function getValue($value)
    {
        ($this->valueCallback)($value);
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }
}
