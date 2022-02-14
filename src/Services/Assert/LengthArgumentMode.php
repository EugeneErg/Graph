<?php declare(strict_types=1);

namespace EugeneErg\Graph\Services\Assert;

class LengthArgumentMode implements ArgumentModeInterface
{
    public function getValue($value): int
    {
        return strlen($value);
    }

    public function getPrefix(): string
    {
        return 'the length of the value of the argument';
    }
}
