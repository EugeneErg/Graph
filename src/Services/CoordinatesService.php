<?php namespace EugeneErg\Graphs\Services;

use EugeneErg\Graphs\ValueObjects\Arc;
use EugeneErg\Graphs\ValueObjects\Point2D;
use EugeneErg\Graphs\ValueObjects\Edge;
use EugeneErg\Graphs\ValueObjects\GravityInterface;
use EugeneErg\Graphs\ValueObjects\Line2D;
use EugeneErg\Graphs\ValueObjects\Topology;

class CoordinatesService
{
    /**
     * @param Topology $topology
     * @param Edge[] $edges
     * @param float $graphRadius
     * @return Point2D[]
     */
    public function getCoordinates(Topology $topology, array $edges, float $graphRadius): array
    {
        $coordinates = $this->getCircleCoordinates($topology->outer, $graphRadius);

        foreach ($topology->arcs as $arc) {
            $this->getArcCoordinates($arc, $coordinates);
        }

        if (count($topology->arcs)) {
            //$this->relax($edges, $topology->outer, $coordinates);
        }

        return $coordinates;
    }

    private function getCircleCoordinates(
        Edge $edge,
        float $graphRadius = 500,
        ?Point2D $center = null,
        float $startAngle = 0
    ): array {
        $result = [];
        $delta = count($edge->vertexes) ? 2 * pi() / count($edge->vertexes) : null;
        $number = 0;

        if ($center === null) {
            $center = new Point2D(.0, .0);
        }

        foreach ($edge->vertexes as $vertex) {
            $angle = $number++ * $delta + $startAngle;
            $result[$vertex] = new Point2D(
                $center->getX() + $graphRadius * -sin($angle),
                $center->getY() + $graphRadius * -cos($angle),
            );
        }

        return $result;
    }

    /**
     * @param Arc $arc
     * @param Point2D[] $coordinates
     */
    private function getArcCoordinates(Arc $arc, array &$coordinates): void
    {
        $indexes = $arc->getIndexes();
        $begin = $coordinates[$arc->firstVertex()];
        $end = $coordinates[$arc->lastVertex()];
        $center = $this->getGravityCenter
        ($arc->getGravity(), $coordinates);

        foreach ($indexes as $vertex => $index) {
            if (!isset($coordinates[$vertex])) {
                $coordinates[$vertex] = $this->bezierPoint(
                    $begin,
                    $center,
                    $end,
                    $index
                );
            }
        }
    }

    private function getGravityCenter(GravityInterface $gravity, array $coordinates): Point2D
    {
        $points = $gravity->toArray();
        $x = 0;
        $y = 0;

        foreach ($points as $point) {
            $coordinate = is_int($point) ? $coordinates[$point] : $this->getGravityCenter($point, $coordinates);
            $x += $coordinate->getX();
            $y += $coordinate->getY();
        }

        return new Point2D(
            $x / count($points),
            $y / count($points)
        );
    }

    private function bezierPoint(Point2D $begin, Point2D $center, Point2D $end, float $index): Point2D
    {
        $index1 = pow(1 - $index, 2);
        $index2 = 2 * $index * (1 - $index);
        $index3 = pow($index, 2);

        return new Point2D(
            $index1 * $begin->getX() + $index2 * $center->getX() + $index3 * $end->getX(),
            $index1 * $begin->getY() + $index2 * $center->getY() + $index3 * $end->getY(),
        );
    }

    /**
     * @param Edge[] $edges
     * @param Point2D[] $coordinates
     */
    private function relax(array $edges, Edge $static, array $coordinates): void
    {
        $fields = [];
        $connections = [];

        foreach ($edges as $edgeNumber => $edge) {
            if ($edge === $static) {
                continue;
            }

            foreach ($edge->vertexes as $vertexNumber => $vertex) {
                $vertexA = $edge->getVertex($vertexNumber - 1);
                $vertexB = $edge->getVertex($vertexNumber + 1);
                $fields[$vertex][$vertexA][$edgeNumber] = $edge;
                $fields[$vertex][$vertexB][$edgeNumber] = $edge;
                $connections[$vertex][$vertexA] = true;
            }
        }

        foreach ($fields as $vertex => $connection) {
            $fields[$vertex] = $this->unionEdges($vertex, $connection, $coordinates);
        }

        foreach ($fields as $vertex => $field) {
            if ($static->findVertex($vertex) !== null) {
                $this->relaxCoordinate($vertex, $field, $connections, $coordinates);
            }
        }
    }

    /**
     * @param int $vertex
     * @param Edge[][] $connections
     * @param Point2D[] $coordinates
     * @return Line2D[]
     */
    private function unionEdges(int $vertex, array $connections, array $coordinates): array
    {
        $edges = [];
        $resultEdge = null;

        foreach ($connections as $vertexB => $connection) {
            reset($connection);
            $edgeNumberA = key($connection);
            end($connection);
            $edgeNumberB = key($connection);
            $edgeA = isset($edges[$edgeNumberA]) ? $resultEdge : $connection[$edgeNumberA];
            $edgeB = isset($edges[$edgeNumberB]) ? $resultEdge : $connection[$edgeNumberB];
            $edges[$edgeNumberA] = true;
            $edges[$edgeNumberB] = true;
            $resultEdge = $this->unionTwoEdges($edgeA, $edgeB, $vertex, $vertexB);
        }

        $result = [];
        $useVertex = [];

        foreach ($resultEdge->vertexes as $number => $vertexA) {
            $vertexB = $resultEdge->getVertex($number + 1);

            if (!isset($useVertex[$vertexB][$vertexA])) {
                $useVertex[$vertexA][$vertexB] = true;
                $result[] = new Line2D($coordinates[$vertexA], $coordinates[$vertexB]);
            }
        }

        return $result;
    }

    private function unionTwoEdges(Edge $edgeA, Edge $edgeB, int $vertexA, int $vertexB): Edge
    {
        $posAA = $edgeA->findVertex($vertexA);
        $posAB = $edgeA->findVertex($vertexB);
        $fromAtoBA = $this->fromAtoB($posAA, $posAB);

        if ($edgeA === $edgeB) {
            return $edgeA->replace([], $fromAtoBA ? $posAB : $posAA, 2);
        }

        $posBA = $edgeB->findVertex($vertexA);
        $posBB = $edgeB->findVertex($vertexB);
        $fromAtoBB = $this->fromAtoB($posBA, $posBB);
        $from = $fromAtoBB ? $posAB : $posAA;
        $vertexes = $fromAtoBA === $fromAtoBB
            ? $edgeA->getVertexes($from, - count($edgeA->vertexes))
            : $edgeA->getVertexes($from);

        return $edgeB->replace($vertexes, $fromAtoBB ? $posBB : $posBA, 2);
    }

    private function fromAtoB(int $posA, int $posB): ?bool
    {
        if ($posA === $posB) {
            return null;
        }

        if ($posA === $posB - 1) {
            return false;
        }

        if ($posA === $posB + 1) {
            return true;
        }

        return $posA < $posB;
    }

    /**
     * @param int $vertex
     * @param Line2D[] $field
     * @param bool[][] $connections
     * @param Point2D[] $coordinates
     */
    private function relaxCoordinate(int $vertex, array $field, array $connections, array $coordinates): void
    {
        foreach ($connections[$vertex] as $vertexB => $true) {
            $field = $this->getVisibleForVisor(
                $coordinates[$vertexB],
                count($connections[$vertexB]) === 2,
                $field
            );
        }

        $field = $this->transformToConvex($vertex, $field, $coordinates);
        $coordinates[$vertex]->__construct(...$this->getCenter($field)->records);
    }

    /**
     * @param Point2D $visor
     * @param bool $visorIsRay
     * @param Line2D[] $field
     * @return Point2D[]
     */
    private function getVisibleForVisor(Point2D $visor, bool $visorIsRay, array $field): array
    {
        $segments = [];
        $result = $field;

        foreach ($field as $numberA => $lineA) {
            for ($numberB = $numberA + 1; $numberB < count($field); $numberB++) {
                unset($result[$numberA], $result[$numberB]);

                $newLines = $this->cutInvisibleForVisor(
                    $visor,
                    $visorIsRay,
                    $lineA,
                    $field[$numberB],
                    $segments[$numberA],
                    $segments[$numberB]
                );
            }
        }

        return $result;
    }

    /**
     * @param int $vertex
     * @param Line2D[] $field
     * @param Point2D[] $coordinates
     * @return Line2D[]
     */
    private function transformToConvex(int $vertex, array $field, array $coordinates): array
    {

    }

    /**
     * @param Line2D[] $field
     * @return Point2D
     */
    private function getCenter(array $field): Point2D
    {

    }

    /**
     * @param Point2D $visor
     * @param bool $visorIsRay
     * @param Line2D $lineA
     * @param Line2D $lineB
     * @return Line2D[]
     */
    /**
     * @param Point2D $visor
     * @param bool $visorIsRay
     * @param Line2D $lineA
     * @param Line2D $lineB
     * @param Point2D[] $segmentsA
     * @param Point2D[] $segmentsB
     * @return Line2D[]
     */
    private function cutInvisibleForVisor(
        Point2D $visor,
        bool $visorIsRay,
        Line2D $lineA,
        Line2D $lineB,
        array &$segmentsA,
        array &$segmentsB
    ): array {
        $visorBelongLineA = $lineA->has($visor, true);
        $visorBelongLineB = $lineB->has($visor, true);

        if (
            ($visorIsRay && ($visorBelongLineA || $visorBelongLineB))
            || ($visorBelongLineA && $visorBelongLineB)
        ) {
            return [$lineA, $lineB];
        }

        if ($visorBelongLineA) {
            return $this->cutInvisibleForLine($lineA, $lineB);
        }

        if ($visorBelongLineB) {
            return $this->cutInvisibleForLine($lineB, $lineA);
        }





        $pointAA = $lineA->getCoordinateA();

        //(y - y1)/(y2-y1) = (x - x1)/(x2-x1);

        //($y - $visor->getY()) / ($pointAA->getY() - $visor->getY())
        //    = ($x - $visor->getX()) / ($pointAA->getX() - $visor->getX());

        $pointBA = $lineB->getCoordinateA();
        $pointBB = $lineB->getCoordinateB();

        //($y - $pointBA->getY()) / ($pointBB->getY() - $pointBA->getY())
        //    = ($x - $pointBA->getX()) / ($pointBB->getX() - $pointBA->getX());



        //тут у нас осталась точка обзора и две линии, на которых она не лежит
    }

    /**
     * @param Line2D $visorLine
     * @param Line2D $line
     * @return Line2D[]
     */
    private function cutInvisibleForLine(Line2D $visorLine, Line2D $line): array
    {
        //Обе линии нарисованны в одну стороно по контуру
    }
}
