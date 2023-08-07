<?php

declare(strict_types=1);

namespace EugeneErg\Graph\New\Actions;

use EugeneErg\Collections\IntegerCollection;
use EugeneErg\Graph\New\Animations\Collections\ColorTrackCollection;
use EugeneErg\Graph\New\Animations\Collections\LineCollection;
use EugeneErg\Graph\New\Animations\Effects\BrushObjectsAroundEffect;
use EugeneErg\Graph\New\Animations\Tracks\ColorTrack;
use EugeneErg\Graph\New\Collections\AnimationGraphCollection;
use EugeneErg\Graph\New\Collections\AnimationVertexCollection;
use EugeneErg\Graph\New\DataTransferObjects\AnimationGraph;
use EugeneErg\Graph\New\DataTransferObjects\AnimationVertex;

class MoveDisconnectedSubGraphAction extends AbstractAction
{
    public function __construct(
        CreateNewGraphAction $parent,
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
        if ($this->vertexes->count() === $parentGraph->vertexes->count()) {
            $graph = $parentGraph;
        } else {
            $vertexes = new AnimationVertexCollection(immutable: false);
            $connections = [];

            foreach ($this->vertexes as $vertexId) {
                $vertexes->set($parentGraph->vertexes[$vertexId], $vertexId);
                $connections[] = $parentGraph->vertexes[$vertexId]->connections;
                unset($parentGraph->vertexes[$vertexId]);
            }

            $graph = new AnimationGraph($vertexes, LineCollection::fromMerge(...$connections)->unique(false));
            $connections = [];

            foreach ($parentGraph->vertexes as $vertexId => $vertex) {
                $connections[] = $parentGraph->vertexes[$vertexId]->connections;
            }

            $parentGraph->connections->splice();
            $parentGraph->connections->push(LineCollection::fromMerge(...$connections)->unique());
            $graphs->splice($graphs->search($parentGraph) + 1, 0, new AnimationGraphCollection([$graph]));
        }

        $startMilliseconds = (new BrushObjectsAroundEffect('#0f0', 300))->apply(
            ColorTrackCollection::fromMap(
                fn (AnimationVertex $vertex): ColorTrack => $vertex->circle->color,
                $graph->vertexes,
            ),
            $startMilliseconds,
        );

        return [$startMilliseconds, $graph];
    }
}
