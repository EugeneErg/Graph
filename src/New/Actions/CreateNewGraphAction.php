<?php

declare(strict_types=1);

namespace EugeneErg\Graph\New\Actions;

use EugeneErg\Collections\IntegerCollection;
use EugeneErg\Graph\New\Animations\Collections\LineCollection;
use EugeneErg\Graph\New\Animations\Collections\Point2DTrackCollection;
use EugeneErg\Graph\New\Animations\DataTransferObjects\Circle;
use EugeneErg\Graph\New\Animations\DataTransferObjects\Line;
use EugeneErg\Graph\New\Animations\Effects\ExpandObjectsAroundEffect;
use EugeneErg\Graph\New\Animations\Tracks\ColorTrack;
use EugeneErg\Graph\New\Animations\Tracks\Point2DTrack;
use EugeneErg\Graph\New\Animations\Tracks\IntegerTrack;
use EugeneErg\Graph\New\Collections\ActionCollection;
use EugeneErg\Graph\New\Collections\AnimationGraphCollection;
use EugeneErg\Graph\New\Collections\IntegerMatrix;
use EugeneErg\Graph\New\DataTransferObjects\AnimationGraph;
use EugeneErg\Graph\New\DataTransferObjects\AnimationVertex;
use EugeneErg\Graph\New\DataTransferObjects\Point2D;
use EugeneErg\Graph\New\Services\CoordinateService;

/**
 * @property-read ActionCollection<MoveDisconnectedSubGraphAction> $children
 */
class CreateNewGraphAction extends AbstractAction
{
    public function __construct(
        public readonly IntegerCollection $vertexes,
        public readonly IntegerMatrix $connections,
        public readonly int $vertexRadius,
    ) {
        parent::__construct();
    }

    public function drawGraph(
        AnimationGraph $parentGraph,
        AnimationGraphCollection $graphs,
        int $startMilliseconds,
    ): array {
        $vertexesCount = $this->vertexes->count();
        $graphRadius = CoordinateService::getRadius($this->vertexRadius * 2, $vertexesCount);
        $graphs->set($parentGraph);

        foreach ($this->vertexes as $vertex) {
            $parentGraph->vertexes->set(new AnimationVertex(
                new Circle(
                    (string) $vertex,
                    new IntegerTrack($this->vertexRadius),
                    new ColorTrack('#fff'),
                    new Point2DTrack(new Point2D()),
                    new IntegerTrack(100),
                ),
                new LineCollection(immutable: false),
            ), $vertex);
        }

        foreach ($this->connections as $vertexA => $connection) {
            foreach ($connection as $vertexB => $value) {
                if ($vertexB > $vertexA) {
                    $line = new Line(
                        new ColorTrack('#000'),
                        $parentGraph->vertexes[$vertexA]->circle->center,
                        $parentGraph->vertexes[$vertexB]->circle->center,
                    );
                    $parentGraph->vertexes[$vertexA]->connections->set($line, $vertexB);
                    $parentGraph->vertexes[$vertexB]->connections->set($line, $vertexA);
                }
            }
        }

        $startMilliseconds = (new ExpandObjectsAroundEffect(
            200,
            $graphRadius,
            shiftAngle: CoordinateService::getAngle($vertexesCount),
        ))->apply(Point2DTrackCollection::fromMap(
            fn (AnimationVertex $vertex): Point2DTrack => $vertex->circle->center,
            $parentGraph->vertexes,
        ), $startMilliseconds);
        $this->sortChildren();

        return [$startMilliseconds, $parentGraph];
    }

    private function sortChildren(): void
    {
        if ($this->children->isEmpty()) {
            return;
        }

        $max = $this->getChildWithMaxVertexCount();
        $pos = $this->children->search($max);
        unset($this->children[$pos]);
        $this->children[] = $max;
    }

    public function getChildWithMaxVertexCount(): MoveDisconnectedSubGraphAction
    {
        return $this->children->reduce(
            fn (
                ?MoveDisconnectedSubGraphAction $result,
                MoveDisconnectedSubGraphAction $next,
            ): MoveDisconnectedSubGraphAction =>
                $result === null || $result->vertexes->count() < $next->vertexes->count() ? $next : $result,
        );
    }
}
