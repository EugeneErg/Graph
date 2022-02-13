<?php declare(strict_types = 1);
namespace EugeneErg\Graph\Processes\SvgAnimation\ValueObject;

use EugeneErg\Graph\Collections\IntegerCollection;
use EugeneErg\Graph\Dto\Point2D;
use EugeneErg\Graph\Processes\Collections\ConnectionsMatrix;
use EugeneErg\Graph\Processes\Collections\VertexesCollection;
use EugeneErg\Graph\ValueObjects\AbstractValueObject;
use EugeneErg\Graph\ValueObjects\Angle;

class Graph extends AbstractValueObject
{
    private VertexesCollection $vertexes;
    private ConnectionsMatrix $connections;
    private int $radius;
    private ?Angle $startOccupiedAngle;
    private ?Angle $occupiedAngle;
    private ?Point2D $center;
    private ?Angle $startAngle;

    public function __construct(
        VertexesCollection $vertexes,
        ConnectionsMatrix $connections,
        int $radius,
        ?Angle $startOccupiedAngle = null,
        ?Angle $occupiedAngle = null,
        ?Point2D $center = null,
        ?Angle $startAngle = null
    ) {
        $this->vertexes = $vertexes;
        $this->connections = $connections;
        $this->radius = $radius;
        $this->startOccupiedAngle = $startOccupiedAngle ?? new Angle();
        $this->occupiedAngle = $occupiedAngle ?? Angle::pi(2);
        $this->center = $center ?? new Point2D();
        $this->startAngle = $startAngle ?? new Angle();
    }

    public function getVertexes(): VertexesCollection
    {
        return $this->vertexes;
    }

    public function getConnections(): ConnectionsMatrix
    {
        return $this->connections;
    }

    public function getSubMatrix(IntegerCollection $vertexes): ConnectionsMatrix
    {
        $result = new ConnectionsMatrix();

        foreach ($this->connections as $vertexA => $connections) {
            if (!$vertexes->has($vertexA, true)) {
                continue;
            }

            foreach ($connections as $vertexB => $connection) {
                if ($vertexes->has($vertexB, true)) {
                    $result->setItem($vertexA, $vertexB, $connections);
                }
            }
        }

        return $result;
    }

    public function getRadius(): int
    {
        return $this->radius;
    }

    public function getOccupiedAngle(): Angle
    {
        return $this->occupiedAngle;
    }

    public function getStartOccupiedAngle(): Angle
    {
        return $this->startOccupiedAngle;
    }

    public function getCenter(): ?Point2D
    {
        return $this->center;
    }

    public function getStartAngle(): ?Angle
    {
        return $this->startAngle;
    }
}
