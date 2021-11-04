<?php declare(strict_types=1);

namespace EugeneErg\tests;

use EugeneErg\Graph\Collections\IntegerCollection;
use EugeneErg\Graph\Collections\IntegerMatrix;
use EugeneErg\Graph\Services\AssertService;
use EugeneErg\Graph\ValueObjects\ClearGraph;

class Helper extends AssertService
{
    public function createClearGraph(IntegerMatrix $graph): ClearGraph
    {
        $connections = $graph->map(function (IntegerCollection $integers): IntegerCollection {
            return $integers->filter(function (int $value): bool {
                return $value !== 0;
            });
        });

        return new ClearGraph($connections);
    }
}
