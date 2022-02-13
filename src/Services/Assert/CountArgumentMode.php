<?php declare(strict_types=1);

namespace EugeneErg\Graph\Services\Assert;

class CountArgumentMode implements ArgumentModeInterface
{
    public function getValue($value): int
    {
        return count($value);
    }

    public function getPrefix($value): string
    {
        return 'the number of elements in argument';
    }
}
