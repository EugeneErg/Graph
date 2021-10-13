<?php declare(strict_types = 1);
namespace EugeneErg\Graph\Services;

use EugeneErg\Graph\ValueObjects\Canvas;

class CanvasService extends AbstractService
{
    public function fill(Canvas $canvas, int $vertex, int $color): array
    {
        $oldColor = $canvas->getColor($vertex);
        $canvas->addVertex($vertex, $color);
        $result[$vertex] = $vertex;
        reset($result);

        while ($vertex !== false) {
            foreach ($canvas->graph->getRow($vertex) as $connectionVertex => $value) {
                if ($canvas->getColor($connectionVertex) === $oldColor) {
                    $canvas->addVertex($connectionVertex, $color);
                    $result[$connectionVertex] = $connectionVertex;
                }
            }

            $vertex = next($result);
        }

        return $result;
    }

    public function pixels(Canvas $canvas, array $vertexes, int $color): void
    {
        foreach ($vertexes as $vertex) {
            $canvas->addVertex($vertex, $color);
            $operations[$vertex] = $vertex;
        }
    }

    public function incFill(Canvas $canvas, int $vertex, int $color): array
    {
        $oldColor = $canvas->getColor($vertex);
        $canvas->addVertex($vertex, $color);
        $result[$vertex] = $vertex;
        reset($result);
        $connect = [];

        while ($vertex !== false) {
            if (!isset($connect[$vertex])) {
                foreach ($canvas->graph->getRow($vertex) as $connectionVertex => $value) {
                    if ($canvas->getColor($connectionVertex) === $oldColor) {
                        $canvas->addVertex($connectionVertex, $color);
                        $result[$connectionVertex] = $connectionVertex;
                    } elseif (
                        $canvas->getColor($connectionVertex) === $color
                        && !isset($result[$connectionVertex])
                    ) {
                        $result[$connectionVertex] = $connectionVertex;
                        $connect[$connectionVertex] = true;
                    }
                }
            }

            $vertex = next($result);
        }

        return $result;
    }
}
