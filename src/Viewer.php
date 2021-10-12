<?php namespace EugeneErg\Graphs;

class Viewer
{
    private const GRAPH_RADIUS = 200;
    private const VERTEX_RADIUS = 10;

    /** @var string */
    private $template;

    public function toSvg(
        array $captions,
        AdjacencyMatrix $matrix,
        int $graphRadius = self::GRAPH_RADIUS,
        int $vertexRadius = self::VERTEX_RADIUS
    ): string {
        $coordinates = [];
        $delta = count($captions) ? 2 * pi() / count($captions) : null;
        $number = 0;

        foreach ($captions as $vertex => $caption) {
            $angle = $number++ * $delta;

            $coordinates[$vertex] = [
                'x' => $graphRadius * sin($angle),
                'y' => $graphRadius * -cos($angle),
            ];
        }

        return $this->returnTemplate('templates/svg', [
            'captions' => $captions,
            'coordinates' => $coordinates,
            'radius' => $vertexRadius,
            'matrix' => $matrix,
            'graphRadius' => $graphRadius,
        ]);
    }

    /**
     * @param Vertex[] $vertexes
     * @param int $graphRadius
     * @param int $vertexRadius
     * @return string
     */
    public function vertexesToSvg(
        array $vertexes,
        int $graphRadius = self::GRAPH_RADIUS,
        int $vertexRadius = self::VERTEX_RADIUS
    ): string {
        return $this->captionsToSvg($this->vertexesToCaptions($vertexes), $graphRadius, $vertexRadius);
    }

    /**
     * @param Vertex[] $vertexes
     * @param AdjacencyMatrix $matrix
     * @param int $graphRadius
     * @param int $vertexRadius
     * @return string
     */
    public function vertexAndMatrixToSvg(
        array $vertexes,
        AdjacencyMatrix $matrix,
        int $graphRadius = self::GRAPH_RADIUS,
        int $vertexRadius = self::VERTEX_RADIUS
    ): string {
        return $this->toSvg($this->vertexesToCaptions($vertexes), $matrix, $graphRadius, $vertexRadius);
    }

    public function matrixToSvg(
        AdjacencyMatrix $matrix,
        int $graphRadius = self::GRAPH_RADIUS,
        int $vertexRadius = self::VERTEX_RADIUS
    ): string {
        $captions = [];

        foreach ($matrix->toArray() as $caption => $connections) {
            $captions[$caption] = $caption;
        }

        return $this->toSvg($captions, $matrix, $graphRadius, $vertexRadius);
    }

    private function returnTemplate(string $template, array $variables = []): string
    {
        $this->template = __DIR__ . '/' . $template . '.php';
        extract($variables);
        ob_start();
        require $this->template;
        return ob_get_clean();
    }

    /**
     * @param Vertex[] $vertexes
     * @return string[]
     */
    private function vertexesToCaptions(array $vertexes): array
    {
        $result = [];

        foreach ($vertexes as $number => $vertex) {
            $result[$number] = $vertex->getName();
        }

        return $result;
    }

    /**
     * @param string[] $captions
     * @param int $graphRadius
     * @param int $vertexRadius
     * @return string
     */
    public function captionsToSvg(
        array $captions,
        int $graphRadius = self::GRAPH_RADIUS,
        int $vertexRadius = self::VERTEX_RADIUS
    ): string {
        end($captions);
        $prevVertex = key($captions);
        $matrix = [];

        foreach ($captions as $number => $caption) {
            $matrix[$number][$prevVertex] = $matrix[$prevVertex][$number] = 1;
            $prevVertex = $number;
        }

        return $this->toSvg($captions, new AdjacencyMatrix($matrix), $graphRadius, $vertexRadius);
    }
}
