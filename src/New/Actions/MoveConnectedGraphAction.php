<?php

declare(strict_types=1);

namespace EugeneErg\Graph\New\Actions;

use EugeneErg\Collections\IntegerCollection;
use EugeneErg\Graph\New\Animations\Collections\ColorTrackCollection;
use EugeneErg\Graph\New\Animations\Collections\DataTransferObjectCollection;
use EugeneErg\Graph\New\Animations\Collections\LineCollection;
use EugeneErg\Graph\New\Animations\DataTransferObjects\Circle;
use EugeneErg\Graph\New\Animations\DataTransferObjects\Line;
use EugeneErg\Graph\New\Animations\Effects\BrushObjectsAroundEffect;
use EugeneErg\Graph\New\Animations\Effects\BrushObjectsEffect;
use EugeneErg\Graph\New\Animations\Segments\IntegerSegment;
use EugeneErg\Graph\New\Animations\Tracks\ColorTrack;
use EugeneErg\Graph\New\Animations\Tracks\IntegerTrack;
use EugeneErg\Graph\New\Collections\AnimationGraphCollection;
use EugeneErg\Graph\New\Collections\AnimationVertexCollection;
use EugeneErg\Graph\New\DataTransferObjects\AnimationGraph;
use EugeneErg\Graph\New\DataTransferObjects\AnimationVertex;

class MoveConnectedGraphAction extends AbstractAction
{
    public function __construct(
        SelectArticulationVertexesAction $parent,
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
        if ($this->parent->children->last() === $this) {
            $startMilliseconds = (new BrushObjectsEffect('#fff', 300))->apply(
                ColorTrackCollection::fromMap(
                    fn (AnimationVertex $vertex): ColorTrack => $vertex->circle->color,
                    $parentGraph->vertexes,
                ),
                $startMilliseconds,
            );

            return [$startMilliseconds, $parentGraph];
        }

        $connectedVertexes = (new IntegerCollection)->fromFlip(IntegerCollection::fromIntersect(
            true,
            false,
            IntegerCollection::fromKeys($parentGraph->vertexes),
            $this->vertexes
        ), false);
        $needVertexes = (new IntegerCollection)->fromFlip($this->vertexes);

        foreach ($connectedVertexes as $vertex => $index) {
            if ($this->vertexesIncludesAllConnections($needVertexes, $parentGraph->vertexes[$vertex]->connections)) {
                unset($connectedVertexes[$vertex]);
            }
        }

        $circles = new DataTransferObjectCollection(immutable: false);

        foreach ($needVertexes as $vertexId => $pos) {
            if (isset($connectedVertexes[$vertexId])) {
                $opacity = new IntegerTrack(0);
                $opacity->addSegment(new IntegerSegment(1, 100), $startMilliseconds);
                $circles[$vertexId] = new Circle(
                    (string) $vertexId,
                    new IntegerTrack($parentGraph->vertexes[$vertexId]->circle->radius->getLastValue()),
                    new ColorTrack($parentGraph->vertexes[$vertexId]->circle->color->getLastValue()),
                    clone $parentGraph->vertexes[$vertexId]->circle->center,
                    $opacity,
                );
            } else {
                $circles[$vertexId] = $parentGraph->vertexes[$vertexId]->circle;
            }
        }

        $vertexes = new AnimationVertexCollection(immutable: false);

        foreach ($needVertexes as $vertexA => $pos) {
            if (isset($connectedVertexes[$vertexA])) {
                //вершина является разделительной
                $vertexConnections = new LineCollection(immutable: false);

                /** @var Line $connection */
                foreach ($parentGraph->vertexes[$vertexA]->connections as $vertexB => $connection) {
                    if (isset($needVertexes[$vertexB])) {//забираем смежную вершину
                        $vertexConnections[$vertexB] = new Line(
                            $connection->color,
                            $circles[$vertexA]->center,
                            $circles[$vertexB]->center,
                        );
                        unset($parentGraph->vertexes[$vertexA]->connections[$vertexB]);
                    }
                }

                $vertexes->set(new AnimationVertex($circles[$vertexA], $vertexConnections), $vertexA);
            } elseif ($this->vertexesIncludesAnyConnections($connectedVertexes, $parentGraph->vertexes[$vertexA]->connections)) {
                //вершина соединяется с разделительной
                $vertexConnections = new LineCollection(immutable: false);

                /** @var Line $connection */
                foreach ($parentGraph->vertexes[$vertexA]->connections as $vertexB => $connection) {
                    if (isset($connectedVertexes[$vertexB])) {//вершина является разделительной
                        $vertexConnections[$vertexB] = new Line(
                            $connection->color,
                            $circles[$vertexA]->center,
                            $circles[$vertexB]->center,
                        );
                    }
                }

                $vertexes->set(new AnimationVertex($circles[$vertexA], $vertexConnections), $vertexA);
                unset($parentGraph->vertexes[$vertexA]);
            } else {
                $vertexes->set($parentGraph->vertexes[$vertexA], $vertexA);
                unset($parentGraph->vertexes[$vertexA]);
            }
        }

        $graph = new AnimationGraph($vertexes);
        $graphs->splice($graphs->search($parentGraph) + 1, 0, new AnimationGraphCollection([$graph]));

        $startMilliseconds = (new BrushObjectsAroundEffect('#0f0', 300))->apply(
            ColorTrackCollection::fromMap(
                fn (AnimationVertex $vertex): ColorTrack => $vertex->circle->color,
                $graph->vertexes,
            ),
            $startMilliseconds,
        );

        (new BrushObjectsEffect('#fff', 300))->apply(
            ColorTrackCollection::fromMap(
                fn (AnimationVertex $vertex): ColorTrack => $vertex->circle->color,
                $graph->vertexes,
            ),
            $startMilliseconds,
        );

        return [$startMilliseconds, $parentGraph];
    }

    /** соединяемся только  указанными вершинами */
    private function vertexesIncludesAllConnections(IntegerCollection $vertexes, LineCollection $connections): bool
    {
        foreach ($connections as $vertex => $connection) {
            if (!isset($vertexes[$vertex])) {
                return false;
            }
        }

        return true;
    }

    private function vertexesIncludesAnyConnections(IntegerCollection $vertexes, LineCollection $connections): bool
    {
        foreach ($vertexes as $vertex => $value) {
            if (isset($connections[$vertex])) {
                return true;
            }
        }

        return false;
    }
}