<?php declare(strict_types=1);

namespace EugeneErg\Graph\Services\Assert;

class TypeArgumentMode implements ArgumentModeInterface
{
    public function getValue($value): string
    {
        return is_object($value) ? get_class($value) : gettype($value);
    }

    public function getPrefix(): string
    {
        return 'the type of the argument';
    }
}
