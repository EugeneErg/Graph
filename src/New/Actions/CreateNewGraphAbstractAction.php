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
use EugeneErg\Graph\New\Animations\Tracks\RadiusTrack;
use EugeneErg\Graph\New\Collections\AnimationGraphCollection;
use EugeneErg\Graph\New\Collections\IntegerMatrix;
use EugeneErg\Graph\New\Collections\AnimationVertexCollection;
use EugeneErg\Graph\New\DataTransferObjects\AnimationGraph;
use EugeneErg\Graph\New\DataTransferObjects\AnimationVertex;
use EugeneErg\Graph\New\DataTransferObjects\Point2D;
use EugeneErg\Graph\New\Services\CoordinateService;

class CreateNewGraphAbstractAction extends AbstractAction
{
    public function __construct(
        public readonly IntegerCollection $vertexes,
        public readonly IntegerMatrix $connections,
        public readonly int $vertexRadius,
    ) {
        parent::__construct();
    }

    public function createNewGraph(): AnimationGraphCollection
    {
        $vertexesCount = $this->vertexes->count();
        $graphRadius = CoordinateService::getRadius($this->vertexRadius * 2, $vertexesCount);
        $result = new AnimationGraph(new AnimationVertexCollection(immutable: false), new LineCollection(immutable: false));
        $graphs = new AnimationGraphCollection([$result]);

        foreach ($this->vertexes as $vertex) {
            $result->vertexes->set(new AnimationVertex(
                new Circle(
                    (string) $vertex,
                    new RadiusTrack($this->vertexRadius),
                    new ColorTrack('#fff'),
                    new Point2DTrack(new Point2D()),
                ),
                new LineCollection(immutable: false),
            ), $vertex);
        }

        foreach ($this->connections as $vertexA => $connection) {
            foreach ($connection as $vertexB => $value) {
                if ($vertexB > $vertexA) {
                    $line = new Line(
                        new ColorTrack('#000'),
                        $result->vertexes[$vertexA]->circle->center,
                        $result->vertexes[$vertexB]->circle->center,
                    );
                    $result->connections->set($line);
                    $result->vertexes[$vertexA]->connections->set($line, $vertexB);
                    $result->vertexes[$vertexB]->connections->set($line, $vertexA);
                }
            }
        }

        (new ExpandObjectsAroundEffect(
            200,
            $graphRadius,
            shiftAngle: CoordinateService::getAngle($vertexesCount),
        ))->apply(Point2DTrackCollection::fromMap(
            fn (AnimationVertex $vertex): Point2DTrack => $vertex->circle->center,
            $result->vertexes,
        ));

        return $this->moveDisconnectedSubGraphs($graphs, $graphRadius);
    }

    private function moveDisconnectedSubGraphs(AnimationGraphCollection $graphs, int $graphRadius): AnimationGraphCollection
    {
        $startMilliseconds = 200 * $this->vertexes->count();

        /** @var MoveDisconnectedSubGraphAction $child */
        foreach ($this->children as $child) {
            $graphs = $child->drawGraph($graphs, 0, $graphRadius, $startMilliseconds);
            $startMilliseconds += 300 * $child->vertexes->count() + 500;
        }

        return $graphs;
    }
}
