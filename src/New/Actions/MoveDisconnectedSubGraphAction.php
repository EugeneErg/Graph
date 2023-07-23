<?php

declare(strict_types=1);

namespace EugeneErg\Graph\New\Actions;

use EugeneErg\Collections\IntegerCollection;

class MoveDisconnectedSubGraphAction
{
    public function __construct(
        public readonly IntegerCollection $vertexes,
        public readonly CreateNewGraphAction $createNewGraphAction,
    ) {
    }
}