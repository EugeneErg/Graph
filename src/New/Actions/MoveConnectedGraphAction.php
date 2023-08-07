<?php

declare(strict_types=1);

namespace EugeneErg\Graph\New\Actions;

use EugeneErg\Collections\IntegerCollection;
use EugeneErg\Graph\New\Collections\AnimationGraphCollection;
use EugeneErg\Graph\New\DataTransferObjects\AnimationGraph;

class MoveConnectedGraphAction extends AbstractAction
{
    public function __construct(SelectArticulationVertexesAction $parent, public readonly IntegerCollection $vertexes)
    {
        parent::__construct($parent);
    }

    public function drawGraph(
        AnimationGraph $parentGraph,
        AnimationGraphCollection $graphs,
        int $startMilliseconds,
    ): array {



        return [$startMilliseconds, $parentGraph];
    }
}