<?php

declare(strict_types=1);

namespace EugeneErg\Graph\New\Actions;

use EugeneErg\Collections\IntegerCollection;
use EugeneErg\Graph\New\Animations\Collections\ColorTrackCollection;
use EugeneErg\Graph\New\Animations\Effects\BrushObjectsEffect;
use EugeneErg\Graph\New\Animations\Tracks\ColorTrack;
use EugeneErg\Graph\New\Collections\AnimationGraphCollection;
use EugeneErg\Graph\New\DataTransferObjects\AnimationGraph;
use EugeneErg\Graph\New\DataTransferObjects\AnimationVertex;

class SelectArticulationVertexesAction extends AbstractAction
{
    public function __construct(
        MoveDisconnectedSubGraphAction $parent,
        public readonly IntegerCollection $vertexes,
        public readonly int $vertexRadius,
    ) {
        parent::__construct($parent);
    }

    public function drawGraph(
        AnimationGraph $parentGraph,
        AnimationGraphCollection $graphs,
        int $startMilliseconds,
    ): array {
        $startMilliseconds = (new BrushObjectsEffect('#fff', 500))->apply(
            ColorTrackCollection::fromMap(
                fn (AnimationVertex $vertex): ColorTrack => $vertex->circle->color,
                $parentGraph->vertexes,
            ),
            $startMilliseconds,
        );
        $colors = new ColorTrackCollection(immutable: false);

        foreach ($this->vertexes as $vertex) {
            $colors[] = $parentGraph->vertexes[$vertex]->circle->color;
        }

        $startMilliseconds = (new BrushObjectsEffect('#0ff', 500))->apply(
            $colors,
            $startMilliseconds,
        );

        /** @var MoveConnectedGraphAction $child * /
        foreach ($this->children as $child) {
            $child->run();
        }

        $startMilliseconds = (new BrushObjectsEffect('#fff', 500))->apply(
            $colors,
            $startMilliseconds,
        );*/

        return [$startMilliseconds, $parentGraph];
    }
}
