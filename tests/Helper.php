<?php declare(strict_types=1);

namespace EugeneErg\Tests;

use EugeneErg\Graph\Collections\Collection;
use EugeneErg\Graph\Collections\EdgeCollection;
use EugeneErg\Graph\Collections\IntegerCollection;
use EugeneErg\Graph\Collections\IntegerMatrix;
use EugeneErg\Graph\Services\AssertService;
use EugeneErg\Graph\ValueObjects\ClearGraph;
use EugeneErg\Graph\ValueObjects\Edge;

class Helper extends AssertService
{
    public function createClearGraph(array $graph): ClearGraph
    {
        return new ClearGraph(IntegerMatrix::fromWalkRecursive(
            IntegerMatrix::fromRecursiveArray($graph),
            fn (int $value): ?int => $value === 0 ? null : $value,
            true
        ));
    }

    public function createEdgeCollection(array $edges): EdgeCollection
    {
        return EdgeCollection::fromMap(function (array $edge): Edge {
            return new Edge(new IntegerCollection($edge));
        }, false, new Collection($edges));
    }
}
