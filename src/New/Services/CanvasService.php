<?php

declare(strict_types=1);

namespace EugeneErg\Graph\New\Services;

use EugeneErg\Collections\CollectionInterface;
use EugeneErg\Collections\IntegerCollection;
use EugeneErg\Graph\New\ValueObjects\Canvas;

class CanvasService
{
    public function fill(Canvas $canvas, int $vertex, int $color): IntegerCollection
    {
        $oldColor = $canvas[$vertex];
        $canvas[$vertex] = $color;
        $result = new IntegerCollection([$vertex => $vertex], immutable: false);

        foreach ($this->getUpdatingIterator($result) as $vertex) {
            foreach ($canvas->graph->getColumn($vertex) ?? [] as $connectionVertex => $value) {
                if ($canvas[$connectionVertex] === $oldColor) {
                    $canvas[$connectionVertex] = $color;
                    $result[$connectionVertex] = $connectionVertex;
                }
            }
        }

        return $result->setImmutable();
    }

    public function getUpdatingIterator(CollectionInterface $collection): \Generator
    {
        for ($i = 0; $i < $collection->count(); $i++) {
            $key = $collection->keyByPosition($i);
            yield $key => $collection[$key];
        }
    }
}
