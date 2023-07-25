<?php

declare(strict_types=1);

namespace EugeneErg\Graph\New\Processes;

use EugeneErg\Graph\New\Actions\CreateNewGraphAbstractAction;
use EugeneErg\Graph\New\Actions\MoveDisconnectedSubGraphAction;
use EugeneErg\Graph\New\Animations\Collections\DataTransferObjectCollection;
use EugeneErg\Graph\New\Animations\DataTransferObjects\DataTransferObjectInterface;
use EugeneErg\Graph\New\DataTransferObjects\AnimationVertex;
use EugeneErg\Graph\New\Events\DisconnectedGraphFoundEvent;
use EugeneErg\Graph\New\Services\EventService;
use EugeneErg\Graph\New\Services\GraphService;
use EugeneErg\Graph\New\ValueObjects\Graph;

class GraphSvgAnimationProcess
{
    public function __construct(
        private readonly EventService $eventService,
        private readonly GraphService $graphService,
    ) {
    }

    public function generate(Graph $graph, int $vertexRadius = 20): DataTransferObjectCollection
    {
        $clearGraph = $graph->clone(false, false);
        $action = $this->getAction($clearGraph, $vertexRadius);
        $animationGraph = $action->createNewGraph();

        return DataTransferObjectCollection::fromMerge(
            $animationGraph->connections,
            DataTransferObjectCollection::fromMap(
                fn (AnimationVertex $vertex): DataTransferObjectInterface => $vertex->circle,
                $animationGraph->vertexes,
            ),
        );
    }

    private function getAction(Graph $graph, int $vertexRadius): CreateNewGraphAbstractAction
    {
        $result = new CreateNewGraphAbstractAction($graph->vertexes, $graph->connections, $vertexRadius);
        $moveDisconnectedSubGraphActions = [];
        $disconnectedGraphFoundListenerId = $this->eventService->listen(
            DisconnectedGraphFoundEvent::class,
            function (DisconnectedGraphFoundEvent $event) use ($result, &$moveDisconnectedSubGraphActions, $vertexRadius): void {
                $moveDisconnectedSubGraphActions[] = new MoveDisconnectedSubGraphAction($result, $event->vertexes, $vertexRadius);
            },
        );
        /*$selectArticulationVertexesActions = [];
        $articulationVertexesFoundListenerId = EventService::instance()->listen(
            ArticulationVertexesFoundEvent::class,
            function (ArticulationVertexesFoundEvent $event)
            use (&$selectArticulationVertexesActions, &$moveDisconnectedSubGraphActions): void {
                $selectArticulationVertexesActions[] = new SelectArticulationVertexesAction(
                    $event->getVertexes(),
                    $moveDisconnectedSubGraphActions[count($selectArticulationVertexesActions)]
                );
            }
        );
        $moveConnectedGraphActions = [];
        $connectedGraphFoundListenerId = EventService::instance()->listen(
            ConnectedGraphFoundEvent::class,
            function (ConnectedGraphFoundEvent $event)
            use (&$moveConnectedGraphActions, &$selectArticulationVertexesActions): void {
                $moveConnectedGraphActions[] = new MoveConnectedGraphAction(
                    $event->getVertexes(),
                    end($selectArticulationVertexesActions)
                );
            }
        );
        TreeService::instance()->createFromGraph($clearGraph);*/
        $this->graphService->splitGraphOnDisconnected($graph);
        $this->eventService->dontListen([
            DisconnectedGraphFoundEvent::class => $disconnectedGraphFoundListenerId,
            //ArticulationVertexesFoundEvent::class => $articulationVertexesFoundListenerId,
            //ConnectedGraphFoundEvent::class => $connectedGraphFoundListenerId,
        ]);

        return $result;
    }
}
