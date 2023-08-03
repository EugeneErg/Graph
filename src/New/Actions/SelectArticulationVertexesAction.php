<?php

declare(strict_types=1);

namespace EugeneErg\Graph\New\Actions;

use EugeneErg\Collections\IntegerCollection;
use EugeneErg\Graph\New\Animations\Collections\ColorTrackCollection;
use EugeneErg\Graph\New\Animations\Effects\BrushObjectsEffect;
use EugeneErg\Graph\New\DataTransferObjects\AnimationGraph;

class SelectArticulationVertexesAction extends AbstractAction
{
    public function __construct(
        MoveDisconnectedSubGraphAction $parent,
        public readonly IntegerCollection $vertexes,
        public readonly int $vertexRadius,
    ) {
        parent::__construct($parent);
    }

    public function brushVertexes(AnimationGraph $graph, int $startMilliseconds): void
    {
        $colors = new ColorTrackCollection(immutable: false);

        foreach ($this->vertexes as $vertex) {
            $colors[] = $graph->vertexes[$vertex]->circle->color;
        }

        $startMilliseconds = (new BrushObjectsEffect('#0ff', 500))->apply(
            $colors,
            $startMilliseconds,
        );
    }
}
