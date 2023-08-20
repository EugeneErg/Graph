<?php

declare(strict_types=1);

namespace EugeneErg\Graph\New\Services;

use EugeneErg\Collections\IntegerCollection;
use EugeneErg\Graph\New\Collections\GraphCollection;
use EugeneErg\Graph\New\Collections\IntegerMatrix;
use EugeneErg\Graph\New\Collections\TreeCollection;
use EugeneErg\Graph\New\Events\ArticulationVertexesFoundEvent;
use EugeneErg\Graph\New\Events\ConnectedGraphFoundEvent;
use EugeneErg\Graph\New\ValueObjects\Canvas;
use EugeneErg\Graph\New\ValueObjects\Graph;
use EugeneErg\Graph\New\ValueObjects\Tree;

class TreeService
{
    public function __construct(
        private readonly GraphService $graphService,
        private readonly ArticulationVertexesFinderService $articulationVertexesFinderService,
        private readonly EventService $eventService,
        private readonly CanvasService $canvasService,
    ) {
    }

    public function createFromGraph(Graph $graph): TreeCollection
    {
        return TreeCollection::fromMap(
            fn (Graph $graph): Tree => $this->fromConnectionGraph($graph),
            $this->graphService->splitGraphOnDisconnected($graph),
        );
    }

    private function fromConnectionGraph(Graph $graph): Tree
    {
        $articulationVertexes = $this->articulationVertexesFinderService->getArticulationVertexesInConnectedGraph($graph);
        $this->eventService->send(new ArticulationVertexesFoundEvent(clone $articulationVertexes));

        if ($articulationVertexes->isEmpty()) {
            return new Tree($graph, new GraphCollection([$graph]));
        }

        $result = new IntegerMatrix(immutable: false);
        $this->split($articulationVertexes, new Canvas($graph), $result);
        $branches = GraphCollection::fromMap(fn (IntegerCollection $vertexes) => $graph->createSupGraph($vertexes), $result);
        $connections = new IntegerMatrix(immutable: false);

        foreach ($result as $key => $values) {
            foreach ($values as $value) {
                if (!isset($connections[$value])) {
                    $connections[$value] = new IntegerCollection(immutable: false);
                }

                $connections[$value][] = $key;
            }
        }

        return new Tree($graph, $branches, $connections);
    }

    private function split(
        IntegerCollection $articulationVertex,
        Canvas $canvas,
        IntegerMatrix $result,
        int $maxColor = 0,
    ): bool {
        $color = $maxColor;
        $hasResult = false;

        foreach ($articulationVertex as $vertexA) {
            if ($canvas[$vertexA] !== $maxColor) {
                continue;
            }

            unset($articulationVertex[$vertexA]);

            foreach($canvas->graph->getColumn($vertexA) ?? [] as $vertexB => $value) {
                if ($canvas[$vertexB] !== $maxColor) {
                    continue;
                }

                $hasResult = true;
                $this->canvasService->pixels($canvas, new IntegerCollection([$vertexA]), ++$color);
                $vertexes = $this->canvasService->fill($canvas, $vertexB, $color)->setImmutable(false);
                $vertexes[$vertexA] = $vertexA;

                if (!$this->split($articulationVertex, $canvas, $result, $color)) {
                    $this->eventService->send(new ConnectedGraphFoundEvent($vertexes));
                    $result[] = $vertexes;
                }
            }
        }

        return $hasResult;
    }
}
