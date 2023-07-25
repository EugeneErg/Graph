<?php

declare(strict_types=1);

namespace EugeneErg\Graph\New\Actions;

use EugeneErg\Collections\IntegerCollection;
use EugeneErg\Graph\New\Animations\Collections\ColorTrackCollection;
use EugeneErg\Graph\New\Animations\Collections\LineCollection;
use EugeneErg\Graph\New\Animations\Collections\Point2DTrackCollection;
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

        foreach ($this->vertexes as $vertexA) {
            $vertexes->set($parentGraph->vertexes[$vertexA], $vertexA);
            $connections[] = $parentGraph->vertexes[$vertexA]->connections;
        }

        $graph = new AnimationGraph($vertexes->setImmutable(), LineCollection::fromMerge(...$connections)->unique());
        $startMilliseconds = (new BrushObjectsEffect('#0f0', 500))->apply(
            ColorTrackCollection::fromMap(
                fn (AnimationVertex $vertex): ColorTrack => $vertex->circle->color,
                $graph->vertexes,
            ),
            $startMilliseconds,
        );
        $startMilliseconds = (new MoveObjectsAroundEffect(
            300,
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
    }
}
