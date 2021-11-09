<?php declare(strict_types=1);

namespace EugeneErg\Tests;

use EugeneErg\Graph\Collections\IntegerMatrix;
use EugeneErg\Graph\Services\AssertService;
use EugeneErg\Graph\ValueObjects\ClearGraph;

class Helper extends AssertService
{
    public function createClearGraph(array $graph): ClearGraph
    {
        return new ClearGraph(IntegerMatrix::fromRecursiveArray($graph)->walkRecursive(function (int $value): ?int {
            return $value === 0 ? null : $value;
        }, true));
    }
}
