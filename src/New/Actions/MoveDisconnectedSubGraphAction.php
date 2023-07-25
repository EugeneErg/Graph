<?php

declare(strict_types=1);

namespace EugeneErg\Graph\New\Actions;

use EugeneErg\Collections\IntegerCollection;
use EugeneErg\Graph\New\Animations\Collections\ColorTrackCollection;
use EugeneErg\Graph\New\Animations\Collections\LineCollection;
use EugeneErg\Graph\New\Animations\Collections\Point2DTrackCollection;
use EugeneErg\Graph\New\Animations\Effects\BrushObjectsAroundEffect;
use EugeneErg\Graph\New\Animations\Effects\BrushObjectsEffect;
use EugeneErg\Graph\New\Animations\Effects\MoveObjectsAroundEffect;
use EugeneErg\Graph\New\Animations\Tracks\ColorTrack;
use EugeneErg\Graph\New\Animations\Tracks\Point2DTrack;
use EugeneErg\Graph\New\Collections\AnimationVertexCollection;
use EugeneErg\Graph\New\DataTransferObjects\AnimationGraph;
use EugeneErg\Graph\New\DataTransferObjects\AnimationVertex;
use EugeneErg\Graph\New\DataTransferObjects\Point2D;
use EugeneErg\Graph\New\Services\CoordinateService;

class MoveDisconnectedSubGraphAction extends AbstractAction
{
    public function __construct(
        CreateNewGraphAbstractAction $parent,
        public readonly IntegerCollection $vertexes,
        public readonly int $vertexRadius,
    ) {
        parent::__construct($parent);
    }

    public function drawGraph(AnimationGraph $parentGraph, Point2D $center, int $radius, ?int $startMilliseconds = null)
    {
        $vertexes = new AnimationVertexCollection(immutable: false);
        $connections = [];

        foreach ($parentGraph->vertexes as $vertexId => $vertex) {
            if ($this->vertexes->has($vertexId)) {
                $vertexes->set($parentGraph->vertexes[$vertexId], $vertexId);
                $connections[] = $parentGraph->vertexes[$vertexId]->connections;
            }
        }

        $graph = new AnimationGraph($vertexes->setImmutable(), LineCollection::fromMerge(...$connections)->unique());
        $startMilliseconds = (new BrushObjectsAroundEffect('#0f0', 300))->apply(
            ColorTrackCollection::fromMap(
                fn (AnimationVertex $vertex): ColorTrack => $vertex->circle->color,
                $graph->vertexes,
            ),
            $startMilliseconds,
        );
        (new MoveObjectsAroundEffect(
            500,
            $radius,
            $center,
            shiftAngle: CoordinateService::getAngle($this->vertexes->count()),
        ))->apply(
            Point2DTrackCollection::fromMap(
                fn (AnimationVertex $vertex): Point2DTrack => $vertex->circle->center,
                $graph->vertexes,
            ),
            $startMilliseconds,
        );
        $startMilliseconds = (new BrushObjectsEffect('#fff', 500))->apply(
            ColorTrackCollection::fromMap(
                fn (AnimationVertex $vertex): ColorTrack => $vertex->circle->color,
                $graph->vertexes,
            ),
            $startMilliseconds,
        );
    }
}
