<?php declare(strict_types = 1);
namespace EugeneErg\Graph\Services;

use EugeneErg\Graph\Collections\BoolCollection;
use EugeneErg\Graph\Collections\IntegerCollection;
use EugeneErg\Graph\ValueObjects\Canvas;

class CanvasService extends AbstractService
{
    public function fill(Canvas $canvas, int $vertex, int $color): IntegerCollection
    {
        $oldColor = $canvas[$vertex];
        $canvas[$vertex] = $color;
        $result = new IntegerCollection([$vertex => $vertex]);

        foreach ($result->getUpdatingIterator() as $vertex) {
            foreach ($canvas->graph->getColumn($vertex, true) ?? [] as $connectionVertex => $value) {
                if ($canvas[$connectionVertex] === $oldColor) {
                    $canvas[$connectionVertex] = $color;
                    $result[$connectionVertex] = $connectionVertex;
                }
            }
        }

        return $result;
    }

    public function pixels(Canvas $canvas, IntegerCollection $vertexes, int $color): void
    {
        foreach ($vertexes as $vertex) {
            $canvas[$vertex] = $color;
            //$operations[$vertex] = $vertex;
        }
    }

    public function incFill(Canvas $canvas, int $vertex, int $color): IntegerCollection
    {
        $oldColor = $canvas[$vertex];
        $canvas[$vertex] = $color;
        $result = new IntegerCollection([$vertex => $vertex]);
        $connect = new BoolCollection();

        foreach ($result->getUpdatingIterator() as $vertex) {
            if (!isset($connect[$vertex])) {
                foreach ($canvas->graph[$vertex] ?? [] as $connectionVertex => $value) {
                    if ($canvas[$connectionVertex] === $oldColor) {
                        $canvas[$connectionVertex] = $color;
                        $result[$connectionVertex] = $connectionVertex;
                    } elseif (
                        $canvas[$connectionVertex] === $color
                        && !isset($result[$connectionVertex])
                    ) {
                        $result[$connectionVertex] = $connectionVertex;
                        $connect[$connectionVertex] = true;
                    }
                }
            }
        }

        return $result;
    }
}
