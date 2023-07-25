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
use EugeneErg\Graph\New\Collections\ActionCollection;
use EugeneErg\Graph\New\Collections\IntegerMatrix;
use EugeneErg\Graph\New\Collections\AnimationVertexCollection;
use EugeneErg\Graph\New\DataTransferObjects\AnimationGraph;
use EugeneErg\Graph\New\DataTransferObjects\AnimationVertex;
use EugeneErg\Graph\New\DataTransferObjects\Point2D;
use EugeneErg\Graph\New\Services\CoordinateService;
use EugeneErg\Graph\New\ValueObjects\Angle;

class CreateNewGraphAbstractAction extends AbstractAction
{
    public function __construct(
        public readonly IntegerCollection $vertexes,
        public readonly IntegerMatrix $connections,
        public readonly int $vertexRadius,
    ) {
        parent::__construct();
    }

    public function createNewGraph(): AnimationGraph
    {
        $vertexesCount = $this->vertexes->count();
        $graphRadius = CoordinateService::getRadius($this->vertexRadius * 2, $vertexesCount);
        $result = new AnimationGraph(new AnimationVertexCollection(immutable: false), new LineCollection(immutable: false));

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

        $this->moveDisconnectedSubGraphs($result, $graphRadius);

        return $result;
    }

    private function moveDisconnectedSubGraphs(AnimationGraph $graph, int $graphRadius): void
    {
        $big = $this->getNumberActionWithMaximumVertexCount();
        /** @var MoveDisconnectedSubGraphAction[]|ActionCollection $actions */
        $actions = clone $this->children;
        $bigAction = $actions[$big];
        unset($actions[$big]);
        $actionsCount = $actions->count();// + $bigAction->getChildren()->count();
        $fullAngle = Angle::degrees($actionsCount < 3 ? -90 : 0);
        $startMilliseconds = 200 * $this->vertexes->count();
        $delta = Angle::degrees(360);
        $data = [];

        foreach ($actions as $number => $action) {
            $childGraphRadius = $actionsCount < 8
                ? $graphRadius
                : CoordinateService::getRadius($this->vertexRadius * 2, $action->vertexes->count());
            $angle = $actionsCount === 1
                ? Angle::degrees(360)
                : CoordinateService::findAnOccupiedAngle($actionsCount < 4 ? 0 : $graphRadius, $childGraphRadius);
            $delta = $delta->minus($angle);
            $data[$number] = [$childGraphRadius, $angle];
        }

        $delta = $delta->divided($actionsCount);

        foreach ($data as $number => [$childGraphRadius, $angle]) {
            $distance = ($actionsCount === 1 ? 0 : $graphRadius) + $childGraphRadius;
            $center = $actionsCount === 3 && $number === 2
                ? new Point2D()
                : $this->getCenter($distance, $fullAngle, $angle);
            $fullAngle = $fullAngle->plus($angle)->plus($delta);
            $actions[$number]->drawGraph($graph, $center, $childGraphRadius, $startMilliseconds);
            $startMilliseconds += 300 * $actions[$number]->vertexes->count() + 500;
        }

        $bigAction->drawGraph($graph, new Point2D(), $graphRadius, $startMilliseconds);
    }

    public function getNumberActionWithMaximumVertexCount(): ?int
    {
        $result = null;

        foreach ($this->children as $number => $action) {
            if ($result === null || $action->vertexes->count() > $this->children[$result]->vertexes->count()) {
                $result = $number;
            }
        }

        return $result;
    }

    private function getCenter(int $distance, Angle $fullAngle, Angle $angle): Point2D
    {
        return CoordinateService::getPoint($distance, $angle->divided(2)->plus($fullAngle));
    }
}
