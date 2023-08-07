<?php

declare(strict_types = 1);

namespace EugeneErg\Graph\New\ValueObjects;

use EugeneErg\Collections\IntegerCollection;
use EugeneErg\Graph\New\Collections\GraphCollection;
use EugeneErg\Graph\New\Collections\IntegerMatrix;

class Tree
{
    public readonly Graph $connections;

    public function __construct(
        public readonly Graph $graph,
        public readonly GraphCollection $branches,
        ?IntegerMatrix $connections = null,
    ) {
        $matrix = new IntegerMatrix(immutable: false);

        foreach ($connections ?? [] as $vertex => $subBranches) {
            foreach ($subBranches as $branchA) {
                $matrix[$branchA] = new IntegerCollection(immutable: false);

                foreach ($subBranches as $branchB) {
                    if ($branchA !== $branchB) {
                        $matrix[$branchA][$branchB] = $vertex;
                    }
                }
            }
        }

        $this->connections = new Graph($matrix);
    }
}
