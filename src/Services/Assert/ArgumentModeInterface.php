<?php declare(strict_types=1);

namespace EugeneErg\Graph\Services\Assert;

interface ArgumentModeInterface
{
    public function getValue($value);
    public function getPrefix($value): string;
}
