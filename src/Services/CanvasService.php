<?php declare(strict_types = 1);
namespace EugeneErg\Graph\Services;

use EugeneErg\Graph\Collections\BoolCollection;
use EugeneErg\Graph\Collections\IntegerCollection;
use EugeneErg\Graph\ValueObjects\Canvas;

class CanvasService extends AbstractService
{
    public function fill(Canvas $canvas, int $vertex, int $color): IntegerCollection
    {
        $oldColor = $canvas->getColor($vertex);
        $canvas->addVertex($vertex, $color);
        $result = new IntegerCollection([$vertex => $vertex]);
        $result->rewind();

        while ($vertex !== null) {
            foreach ($canvas->graph->connections[$vertex] ?? [] as $connectionVertex => $value) {
                if ($canvas->getColor($connectionVertex) === $oldColor) {
                    $canvas->addVertex($connectionVertex, $color);
                    $result[$connectionVertex] = $connectionVertex;
                }
            }

            $vertex = $result->next();
        }

        return $result;
    }

    public function pixels(Canvas $canvas, IntegerCollection $vertexes, int $color): void
    {
        foreach ($vertexes as $vertex) {
            $canvas->addVertex($vertex, $color);
            $operations[$vertex] = $vertex;
        }
    }

    public function incFill(Canvas $canvas, int $vertex, int $color): IntegerCollection
    {
        $oldColor = $canvas->getColor($vertex);
        $canvas->addVertex($vertex, $color);
        $result = new IntegerCollection([$vertex => $vertex]);
        $result->rewind();
        $connect = new BoolCollection();

        while ($vertex !== null) {
            if (!isset($connect[$vertex])) {
                foreach ($canvas->graph->connections[$vertex] ?? [] as $connectionVertex => $value) {
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

            $vertex = $result->next();
        }

        return $result;
    }
}
