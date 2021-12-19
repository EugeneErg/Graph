<?php declare(strict_types = 1);
namespace EugeneErg\Graph\Services;

use EugeneErg\Graph\Collections\GraphCollection;
use EugeneErg\Graph\Collections\IntegerCollection;
use EugeneErg\Graph\Collections\IntegerMatrix;
use EugeneErg\Graph\ValueObjects\AbstractGraph;
use EugeneErg\Graph\ValueObjects\Canvas;
use EugeneErg\Graph\ValueObjects\ClearGraph;

class GraphService extends AbstractService
{
    public function getArticulationVertex(ClearGraph $graph): IntegerCollection
    {
        $result = new IntegerMatrix();

        /** @var ClearGraph $connectedGraphs */
        foreach ($this->splitGraphOnDisconnected($graph) as $connectedGraphs) {
            $result[] = ArticulationVertexesFinderService::instance()
                ->getArticulationVertexesInConnectedGraph($connectedGraphs);
        }

        return count($result) ? IntegerCollection::fromReplace(...$result) : new IntegerCollection();
    }

    public function splitGraphOnDisconnected(AbstractGraph $graph): GraphCollection
    {
        if ($graph->vertexes->isEmpty()) {
            return new GraphCollection();
        }

        $graph = ClearGraph::fromGraph($graph);
        $canvas = new Canvas($graph);
        $operations = new IntegerMatrix();

        foreach ($graph->vertexes as $vertex) {
            if ($canvas[$vertex] === 0) {
                $operations->setCollection(null, CanvasService::instance()->fill($canvas, $vertex, 1));
            }
        }

        if ($operations->count() === 1) {
            return new GraphCollection([$graph]);
        }

        return GraphCollection::fromMap(function(IntegerCollection $operation) use ($graph): ClearGraph {
            return $graph->createSupGraph($operation);
        }, false, $operations);
    }
}
