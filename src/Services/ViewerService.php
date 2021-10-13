<?php namespace EugeneErg\Graph\Services;

use EugeneErg\Graph\ValueObjects\ClearGraph;
use EugeneErg\Graph\ValueObjects\Graph;
use EugeneErg\Graph\ValueObjects\GravityInterface;
use EugeneErg\Graph\ValueObjects\Point2D;
use EugeneErg\Graph\ValueObjects\Polygon;
use EugeneErg\Graph\ValueObjects\Topology;

class ViewerService
{
    /** @var CoordinatesService */
    private $coordinatesService;

    public function __construct()
    {
        $this->coordinatesService = new CoordinatesService();
    }

    private const GRAPH_RADIUS = 200;
    private const VERTEX_RADIUS = 5;

    /** @var string */
    private $template;

    public function toSvg(
        string $caption,
        Graph $graph,
        int $graphRadius = self::GRAPH_RADIUS,
        int $vertexRadius = self::VERTEX_RADIUS
    ): string {
        return $this->returnTemplate('templates/svg', [
            'coordinates' => $this->getCircleCoordinates($graph->vertexes, $graphRadius),
            'radius' => $vertexRadius,
            'graph' => $graph,
            'graphRadius' => $graphRadius,
            'caption' => $caption,
        ]);
    }

    /**
     * @param string $caption
     * @param int[] $vertexes
     * @param array|null $connections
     * @param int $graphRadius
     * @param int $vertexRadius
     * @return string
     */
    public function vertexesToSvg(
        string $caption,
        array $vertexes,
        ?array $connections = null,
        int $graphRadius = self::GRAPH_RADIUS,
        int $vertexRadius = self::VERTEX_RADIUS
    ): string {
        return $this->toSvg(
            $caption,
            new ClearGraph($connections ?? $this->getCircleConnections($vertexes), $vertexes),
            $graphRadius,
            $vertexRadius
        );
    }

    private function returnTemplate(string $template, array $variables = []): string
    {
        ob_start();
        $this->echoTemplate($template, $variables);

        return ob_get_clean();
    }

    private function echoTemplate(string $template, array $variables = []): void
    {
        $this->template = __DIR__ . '/' . $template . '.php';
        extract($variables);

        require $this->template;
    }

    public function topologyToSvg(
        string $caption,
        Topology $topology,
        Graph $graph,
        array $edges,
        int $graphRadius = self::GRAPH_RADIUS,
        int $vertexRadius = self::VERTEX_RADIUS
    ): string {
        $coordinates = $this->coordinatesService->getCoordinates($topology, $edges, $graphRadius);

        return $this->returnTemplate('templates/svg', [
            'coordinates' => $coordinates,
            'radius' => $vertexRadius,
            'graph' => $graph,
            'graphRadius' => $graphRadius,
            'caption' => $caption,
        ]);
    }

    public function polygonToCsv(string $caption, Polygon $polygon): string
    {
        $vertexes = array_keys($polygon->records);
        $radius = 0;

        /** @var Point2D $point */
        foreach ($polygon->records as $point) {
            $radius = max(abs($point->getX()), abs($point->getY()), $radius);
        }

        return $this->returnTemplate('templates/svg', [
            'coordinates' => $polygon->records,
            'radius' => $radius / 30,
            'graph' => new ClearGraph($this->getCircleConnections($vertexes), $vertexes),
            'graphRadius' => $radius,
            'caption' => $caption,
        ]);
    }

    private function bezierPoint($p0, $p1, $p2, $t): array
    {
        $t1 = pow(1 - $t, 2);
        $t2 = 2 * $t * (1 - $t);
        $t3 = pow($t, 2);

        return [
            'x' => $t1 * $p0['x'] + $t2 * $p1['x'] + $t3 * $p2['x'],
            'y' => $t1 * $p0['y'] + $t2 * $p1['y'] + $t3 * $p2['y'],
        ];
    }

    private function getCircleCoordinates(array $vertexes, int $graphRadius): array
    {
        $coordinates = [];
        $delta = count($vertexes) ? 2 * pi() / count($vertexes) : null;
        $number = 0;

        foreach ($vertexes as $vertex) {
            $angle = $number++ * $delta;
            $coordinates[$vertex] = [
                'x' => $graphRadius * -sin($angle),
                'y' => $graphRadius * -cos($angle),
            ];
        }

        return $coordinates;
    }

    private function getCircleConnections(array $vertexes)
    {
        $prevVertex = end($vertexes);
        $connections = [];

        foreach ($vertexes as $vertex) {
            $connections[$vertex][$prevVertex] = 1;
            $prevVertex = $vertex;
        }

        return $connections;
    }

    private function getArcConnections(array $vertexes)
    {
        $result = $this->getCircleConnections($vertexes);

        if (count($vertexes) > 2) {
            unset(
                $result[reset($vertexes)][end($vertexes)],
                $result[end($vertexes)][reset($vertexes)]
            );
        }

        return $result;
    }

    private function getIndexes(array $vertexes, $from = 0, $to = 1, array &$result = []): array
    {
        if (is_array($vertexes[0]) && count($vertexes) === 1) {
            $vertexes = $vertexes[0];
        }

        $count = 0;

        foreach ($vertexes as $vertex) {
            $count += is_array($vertex) ? 2 : 1;
        }

        if ($count === 1) {
            $result[$vertex] = $from;

            return $result;
        }

        $step = ($to - $from) / ($count - 1);
        $pos = $from;

        foreach ($vertexes as $partVertexes) {
            if (!is_array($partVertexes)) {
                $result[$partVertexes] = $pos;
                $pos += $step;
            } else {
                self::getIndexes($partVertexes, $pos, $pos + $step, $result);
                $pos += $step * 2;
            }
        }

        return $result;
    }

    private function getGravityCenter(GravityInterface $gravity, array $coordinates): array
    {
        $points = $gravity->toArray();
        $x = 0;
        $y = 0;

        foreach ($points as $point) {
            $coordinate = is_int($point) ? $coordinates[$point] : $this->getGravityCenter($point, $coordinates);
            $x += $coordinate['x'];
            $y += $coordinate['y'];
        }

        return [
            'x' => $x / count($points),
            'y' => $y / count($points),
        ];
    }
}