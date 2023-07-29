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
use EugeneErg\Graph\New\Collections\AnimationGraphCollection;
use EugeneErg\Graph\New\Collections\AnimationVertexCollection;
use EugeneErg\Graph\New\DataTransferObjects\AnimationGraph;
use EugeneErg\Graph\New\DataTransferObjects\AnimationVertex;
use EugeneErg\Graph\New\DataTransferObjects\Point2D;
use EugeneErg\Graph\New\Services\CoordinateService;
use EugeneErg\Graph\New\ValueObjects\Angle;

class MoveDisconnectedSubGraphAction extends AbstractAction
{
    public function __construct(
        CreateNewGraphAbstractAction $parent,
        public readonly IntegerCollection $vertexes,
        public readonly int $vertexRadius,
    ) {
        parent::__construct($parent);
    }

    public function drawGraph(
        AnimationGraphCollection $graphs,
        int $graphNumber,
        int $graphRadius,
        ?int $startMilliseconds = null,
    ): AnimationGraphCollection {
        if ($this->vertexes->count() === $graphs[$graphNumber]->vertexes->count()) {
            $graph = $graphs[$graphNumber];
        } else {
            $vertexes = new AnimationVertexCollection(immutable: false);
            $connections = [];
            $changeVertexes = (clone $graphs[$graphNumber]->vertexes)->setImmutable(false);

            foreach ($this->vertexes as $vertexId) {
                $vertexes->set($changeVertexes[$vertexId], $vertexId);
                $connections[] = $changeVertexes[$vertexId]->connections;
                unset($changeVertexes[$vertexId]);
            }

            $graph = new AnimationGraph($vertexes->setImmutable(), LineCollection::fromMerge(...$connections)->unique());
            $connections = [];

            foreach ($changeVertexes as $vertexId => $vertex) {
                $connections[] = $changeVertexes[$vertexId]->connections;
            }

            $changeGraph = new AnimationGraph(
                $changeVertexes->setImmutable(),
                LineCollection::fromMerge(...$connections)->unique(),
            );
            $graphs = $graphs->set($graph)->set($changeGraph, 0);
        }

        $startMilliseconds = (new BrushObjectsAroundEffect('#0f0', 300))->apply(
            ColorTrackCollection::fromMap(
                fn (AnimationVertex $vertex): ColorTrack => $vertex->circle->color,
                $graph->vertexes,
            ),
            $startMilliseconds,
        );

        if (isset($changeGraph)) {
            $this->relaxGraphs($graphs, $startMilliseconds, $graphRadius);
        }

        $startMilliseconds = (new BrushObjectsEffect('#fff', 500))->apply(
            ColorTrackCollection::fromMap(
                fn (AnimationVertex $vertex): ColorTrack => $vertex->circle->color,
                $graph->vertexes,
            ),
            $startMilliseconds,
        );

        return $graphs;
    }

    private function relaxGraphs(AnimationGraphCollection $graphs, int $startMilliseconds, int $graphRadius): int
    {
        if ($graphs->count() === 0) {
            return $startMilliseconds;
        }

        if ($graphs->count() === 1) {
            return (new MoveObjectsAroundEffect(
                500,
                CoordinateService::getRadius($this->vertexRadius * 2, $graphs->first()->vertexes->count()),
                new Point2D(),
                shiftAngle: CoordinateService::getAngle($graphs->first()->vertexes->count()),
            ))->apply(
                Point2DTrackCollection::fromMap(
                    fn (AnimationVertex $vertex): Point2DTrack => $vertex->circle->center,
                    $graphs->first()->vertexes,
                ),
                $startMilliseconds,
            );
        }

        $delta = Angle::pi(2);
        $data = [];

        foreach ($graphs as $graphNumber => $graph) {
            $childGraphRadius = $graphs->count() < 7
                ? $graphRadius
                : CoordinateService::getRadius($this->vertexRadius * 2, $graph->vertexes->count());
            $angle = CoordinateService::findAnOccupiedAngle($graphRadius, $childGraphRadius);
            $delta = $delta->minus($angle);
            $data[$graphNumber] = [$graphNumber, $childGraphRadius, $angle];
        }

        $delta = $delta->divided($graphs->count());
        $fullAngle = Angle::degrees($graphs->count() < 3 ? -90 : 0);

        foreach ($data as [$graphNumber, $childGraphRadius, $angle]) {
            $distance = $graphRadius + $childGraphRadius;
            $center = $this->getCenter($distance, $fullAngle, $angle);
            $fullAngle = $fullAngle->plus($angle)->plus($delta);
            (new MoveObjectsAroundEffect(
                500,
                CoordinateService::getRadius($this->vertexRadius * 2, $graphs[$graphNumber]->vertexes->count()),
                $center,
                shiftAngle: CoordinateService::getAngle($graphs[$graphNumber]->vertexes->count()),
            ))->apply(
                Point2DTrackCollection::fromMap(
                    fn (AnimationVertex $vertex): Point2DTrack => $vertex->circle->center,
                    $graphs[$graphNumber]->vertexes,
                ),
                $startMilliseconds,
            );
        }

        return $startMilliseconds + 500;
    }

    private function getCenter(int $distance, Angle $fullAngle, Angle $angle): Point2D
    {
        return CoordinateService::getPoint($distance, $angle->divided(2)->plus($fullAngle));
    }
}
