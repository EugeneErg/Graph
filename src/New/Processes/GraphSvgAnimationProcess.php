<?php

declare(strict_types=1);

namespace EugeneErg\Graph\New\Processes;

use EugeneErg\Graph\New\Actions\CreateNewGraphAction;
use EugeneErg\Graph\New\Actions\MoveDisconnectedSubGraphAction;
use EugeneErg\Graph\New\Animations\Animators\AnimatorInterface;
use EugeneErg\Graph\New\Animations\Collections\DataTransferObjectCollection;
use EugeneErg\Graph\New\Events\DisconnectedGraphFoundEvent;
use EugeneErg\Graph\New\Services\EventService;
use EugeneErg\Graph\New\ValueObjects\Graph;

class GraphSvgAnimationProcess
{
    public function __construct(
        private readonly EventService $eventService,
        private readonly AnimatorInterface $animator,
    ) {
    }

    public function generate(Graph $graph, int $vertexRadius = 20)
    {
        $clearGraph = $graph->clone(false, false);
        $action = $this->getAction($clearGraph, $vertexRadius);
        $animationGraph = $action->createNewGraph();
        $objects = DataTransferObjectCollection::fromMerge($animationGraph->connections, $animationGraph->vertexes);
        echo $this->animator->generateContent($objects);
    }

    private function getAction(Graph $graph, int $vertexRadius): CreateNewGraphAction
    {
        $result = new CreateNewGraphAction($graph->vertexes, $graph->connections, $vertexRadius);
        $moveDisconnectedSubGraphActions = [];
        $disconnectedGraphFoundListenerId = $this->eventService->listen(
            DisconnectedGraphFoundEvent::class,
            function (DisconnectedGraphFoundEvent $event) use ($result, &$moveDisconnectedSubGraphActions): void {
                $moveDisconnectedSubGraphActions[] = new MoveDisconnectedSubGraphAction($event->vertexes, $result);
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
        $this->eventService->dontListen([
            DisconnectedGraphFoundEvent::class => $disconnectedGraphFoundListenerId,
            //ArticulationVertexesFoundEvent::class => $articulationVertexesFoundListenerId,
            //ConnectedGraphFoundEvent::class => $connectedGraphFoundListenerId,
        ]);

        return $result;
    }
}
