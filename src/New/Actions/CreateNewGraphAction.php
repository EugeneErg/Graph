<?php

declare(strict_types=1);

namespace EugeneErg\Graph\New\Actions;

use EugeneErg\Collections\IntegerCollection;
use EugeneErg\Graph\New\Animations\Collections\ConnectionCollection;
use EugeneErg\Graph\New\Animations\Collections\Point2DTrackCollection;
use EugeneErg\Graph\New\Animations\Collections\VertexCollection;
use EugeneErg\Graph\New\Animations\DataTransferObjects\Line;
use EugeneErg\Graph\New\Animations\DataTransferObjects\Circle;
use EugeneErg\Graph\New\Animations\Effects\ExpandObjectsAroundEffect;
use EugeneErg\Graph\New\Animations\Tracks\ColorTrack;
use EugeneErg\Graph\New\Animations\Tracks\Point2DTrack;
use EugeneErg\Graph\New\Animations\Tracks\RadiusTrack;
use EugeneErg\Graph\New\Collections\IntegerMatrix;
use EugeneErg\Graph\New\DataTransferObjects\Point2D;
use EugeneErg\Graph\New\Services\CoordinateService;
use EugeneErg\Graph\New\Animations\DataTransferObjects\Graph;

class CreateNewGraphAction implements ActionInterface
{
    public function __construct(
        public readonly IntegerCollection $vertexes,
        public readonly IntegerMatrix $connections,
        public readonly int $vertexRadius,
    ) {
    }

    public function createNewGraph(): Graph {
        $vertexesCount = $this->vertexes->count();
        $angle = CoordinateService::getAngle($vertexesCount);
        $graphRadius = CoordinateService::getRadius($this->vertexRadius * 2, $vertexesCount);
        $result = new Graph(new VertexCollection(immutable: false), new ConnectionCollection(immutable: false));

        foreach ($this->vertexes as $vertex) {
            $result->vertexes->set(new Circle(
                (string) $vertex,
                new RadiusTrack($this->vertexRadius),
                new ColorTrack('#fff'),
                new Point2DTrack(new Point2D()),
            ), $vertex);
        }

        foreach ($this->connections as $vertexA => $connection) {
            foreach ($connection as $vertexB => $value) {
                if ($vertexB > $vertexA) {
                    $result->connections->set(new Line(
                        new ColorTrack('#000'),
                        $result->vertexes[$vertexA]->center,
                        $result->vertexes[$vertexB]->center,
                    ));
                }
            }
        }

        (new ExpandObjectsAroundEffect(100, $graphRadius, shiftAngle: $angle))->apply(
            Point2DTrackCollection::fromMap(fn (Circle $vertex): Point2DTrack => $vertex->center, $result->vertexes),
        );

        return $result;
    }
}
