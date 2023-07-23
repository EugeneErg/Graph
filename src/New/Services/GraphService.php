<?php

declare(strict_types=1);

namespace EugeneErg\Graph\New\Services;

use EugeneErg\Collections\IntegerCollection;
use EugeneErg\Graph\New\Collections\GraphCollection;
use EugeneErg\Graph\New\Collections\IntegerMatrix;
use EugeneErg\Graph\New\Events\DisconnectedGraphFoundEvent;
use EugeneErg\Graph\New\ValueObjects\Canvas;
use EugeneErg\Graph\New\ValueObjects\Graph;

class GraphService
{
    public function __construct(
        private readonly CanvasService $canvasService,
        private readonly EventService $eventService,
    ) {
    }

    public function splitGraphOnDisconnected(Graph $graph): GraphCollection
    {
        if ($graph->vertexes->isEmpty()) {
            return new GraphCollection();
        }

        $graph = $graph->clone(false, false);
        $canvas = new Canvas($graph);
        $operations = new IntegerMatrix(immutable: false);

        foreach ($graph->vertexes as $vertex) {
            if ($canvas[$vertex] === 0) {
                $vertexes = $this->canvasService->fill($canvas, $vertex, 1);
                $this->eventService->send(new DisconnectedGraphFoundEvent($vertexes));
                $operations[] = $vertexes;
            }
        }

        if ($operations->count() === 1) {
            return new GraphCollection([$graph]);
        }

        return GraphCollection::fromMap(function(IntegerCollection $operation) use ($graph): Graph {
            return $graph->createSupGraph($operation);
        }, $operations);
    }
}