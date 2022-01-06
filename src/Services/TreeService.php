<?php declare(strict_types = 1);
namespace EugeneErg\Graph\Services;

use EugeneErg\Graph\Collections\GraphCollection;
use EugeneErg\Graph\Collections\IntegerCollection;
use EugeneErg\Graph\Collections\IntegerMatrix;
use EugeneErg\Graph\Collections\TreeCollection;
use EugeneErg\Graph\Events\ArticulationVertexesFoundEvent;
use EugeneErg\Graph\Events\ConnectedGraphFoundEvent;
use EugeneErg\Graph\ValueObjects\AbstractGraph;
use EugeneErg\Graph\ValueObjects\Canvas;
use EugeneErg\Graph\ValueObjects\ClearGraph;
use EugeneErg\Graph\ValueObjects\Tree;

class TreeService extends AbstractService
{
    public function createFromGraph(AbstractGraph $graph): TreeCollection
    {
        return TreeCollection::fromMap(function (ClearGraph $graph): Tree {
            return TreeService::instance()->fromConnectionGraph($graph);
        }, false, GraphService::instance()->splitGraphOnDisconnected($graph));
    }

    private function fromConnectionGraph(ClearGraph $graph): Tree
    {
        $articulationVertexes = ArticulationVertexesFinderService::instance()
            ->getArticulationVertexesInConnectedGraph($graph);
        EventService::instance()->send(new ArticulationVertexesFoundEvent(clone $articulationVertexes));

        if ($articulationVertexes->isEmpty()) {
            return new Tree($graph, new GraphCollection([$graph]));
        }

        $result = new IntegerMatrix();
        $this->split($articulationVertexes, new Canvas($graph), $result);
        $branches = GraphCollection::fromMap(function (IntegerCollection $vertexes) use ($graph): AbstractGraph {
            return $graph->createSupGraph($vertexes);
        }, false, $result);
        $connections = new IntegerMatrix();

        foreach ($result->listBy(2) as [$value1, $value2]) {
            $connections->setItem($value2->value, null, (int) $value1->key);
        }

        return new Tree($graph, $branches, $connections);
    }

    private function split(
        IntegerCollection $articulationVertex,
        Canvas $canvas,
        IntegerMatrix $result,
        int $maxColor = 0
    ): bool {
        $color = $maxColor;
        $hasResult = false;

        foreach ($articulationVertex as $vertexA) {
            if ($canvas[$vertexA] !== $maxColor) {
                continue;
            }

            unset($articulationVertex[$vertexA]);

            foreach($canvas->graph->getColumn($vertexA, true) ?? [] as $vertexB => $value) {
                if ($canvas[$vertexB] !== $maxColor) {
                    continue;
                }

                $hasResult = true;
                CanvasService::instance()->pixels($canvas, new IntegerCollection([$vertexA]), ++$color);
                $vertexes = CanvasService::instance()->fill($canvas, $vertexB, $color);
                $vertexes[$vertexA] = $vertexA;

                if (!$this->split($articulationVertex, $canvas, $result, $color)) {
                    EventService::instance()->send(new ConnectedGraphFoundEvent($vertexes));
                    $result->setCollection(null, $vertexes);
                }
            }
        }

        return $hasResult;
    }
}
