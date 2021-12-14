<?php declare(strict_types=1);

namespace EugeneErg\Tests;

use EugeneErg\Graph\Collections\BoolMatrix;
use EugeneErg\Graph\Collections\IntegerMatrix;
use EugeneErg\Graph\Services\AssertService;
use EugeneErg\Graph\ValueObjects\ClearGraph;

class Helper extends AssertService
{
    public function createClearGraph(array $graph): ClearGraph
    {
        return new ClearGraph(BoolMatrix::fromWalkRecursive(
            IntegerMatrix::fromRecursiveArray($graph),
            fn (int $value): ?bool => $value === 0 ? null : (bool) $value,
            true
        ));
    }
}
