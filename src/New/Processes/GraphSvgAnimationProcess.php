<?php

declare(strict_types=1);

namespace EugeneErg\Graph\New\Processes;

use EugeneErg\Graph\New\Actions\CreateNewGraphAbstractAction;
use EugeneErg\Graph\New\Actions\MoveDisconnectedSubGraphAction;
use EugeneErg\Graph\New\Actions\SelectArticulationVertexesAction;
use EugeneErg\Graph\New\Animations\Collections\DataTransferObjectCollection;
use EugeneErg\Graph\New\Animations\DataTransferObjects\DataTransferObjectInterface;
use EugeneErg\Graph\New\Collections\ActionCollection;
use EugeneErg\Graph\New\DataTransferObjects\AnimationGraph;
use EugeneErg\Graph\New\DataTransferObjects\AnimationVertex;
use EugeneErg\Graph\New\Events\ArticulationVertexesFoundEvent;
use EugeneErg\Graph\New\Events\DisconnectedGraphFoundEvent;
use EugeneErg\Graph\New\Services\ArticulationVertexesFinderService;
use EugeneErg\Graph\New\Services\EventService;
use EugeneErg\Graph\New\Services\GraphService;
use EugeneErg\Graph\New\ValueObjects\Graph;

class GraphSvgAnimationProcess
{
    public function __construct(
        private readonly EventService $eventService,
        private readonly GraphService $graphService,
        private readonly ArticulationVertexesFinderService $articulationVertexesFinderService,
    ) {
    }

    public function generate(Graph $graph, int $vertexRadius = 20): DataTransferObjectCollection
    {
        $clearGraph = $graph->clone(false, false);
        $action = $this->getAction($clearGraph, $vertexRadius);
        $animationGraphs = $action->createNewGraph();
        $forMerge = [];

        foreach ($animationGraphs as $animationGraph) {
            $forMerge[] = $animationGraph->connections;
            $forMerge[] = DataTransferObjectCollection::fromMap(
                fn (AnimationVertex $vertex): DataTransferObjectInterface => $vertex->circle,
                $animationGraph->vertexes,
            );
        }

        return DataTransferObjectCollection::fromMerge(...$forMerge);
    }

    private function getAction(Graph $graph, int $vertexRadius): CreateNewGraphAbstractAction
    {
        $result = new CreateNewGraphAbstractAction($graph->vertexes, $graph->connections, $vertexRadius);
        $moveDisconnectedSubGraphActions = new ActionCollection(immutable: false);
        $disconnectedGraphFoundListenerId = $this->eventService->listen(
            DisconnectedGraphFoundEvent::class,
            function (DisconnectedGraphFoundEvent $event)
            use ($result, $moveDisconnectedSubGraphActions, $vertexRadius): void {
                $moveDisconnectedSubGraphActions[] = new MoveDisconnectedSubGraphAction(
                    $result,
                    $event->vertexes,
                    $vertexRadius,
                );
            },
        );
        $selectArticulationVertexesActions = new ActionCollection(immutable: false);
        $articulationVertexesFoundListenerId = $this->eventService->listen(
            ArticulationVertexesFoundEvent::class,
            function (ArticulationVertexesFoundEvent $event)
            use ($selectArticulationVertexesActions, $moveDisconnectedSubGraphActions, $vertexRadius): void {
                $selectArticulationVertexesActions[] = new SelectArticulationVertexesAction(
                    $moveDisconnectedSubGraphActions[$selectArticulationVertexesActions->count()],
                    $event->vertexes,
                    $vertexRadius,
                );
            }
        );
        /*$moveConnectedGraphActions = [];
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
        foreach ($this->graphService->splitGraphOnDisconnected($graph) as $subGraph) {
            $articulationVertexes = $this->articulationVertexesFinderService
                ->getArticulationVertexesInConnectedGraph($subGraph);
            $this->eventService->send(new ArticulationVertexesFoundEvent($articulationVertexes));
        }

        $this->eventService->dontListen([
            DisconnectedGraphFoundEvent::class => $disconnectedGraphFoundListenerId,
            ArticulationVertexesFoundEvent::class => $articulationVertexesFoundListenerId,
            //ConnectedGraphFoundEvent::class => $connectedGraphFoundListenerId,
        ]);

        return $result;
    }
}

/**
 * R^2 + R(r1+r2) - r1*r2 - cos(a) * (R^2 + R(r1+r2) + r1*r2) = 0
 * R^2(1-cos(a)) + R(r1+r2)(1-cos(a)) - (r1*r2)(1 + cos(a)) = 0
 *
 * D = (r1+r2)^2(1-cos(a))^2 + 4(1-cos^2(a))(r1*r2)
 *
 * D = (r1+r2)^2(1-cos(a))^2 + 4 * sin^2(a) * r1 * r2
 * D = (r1^2 + r2^2 + 2 * r1 * r2) * (1 - 2 * cos(a) + cos^2(a)) + 4 * r1 * r2 - 4 * cos^2(a) * r1 * r2
 *
 *
 * + r1^2
 * + r2^2
 * + (r1^2 + r2^2) * cos^2(a)
 * - 2 * (r1^2 + r2^2) * cos(a)
 * - 4 * cos(a) * r1 * r2
 * + 6 * r1 * r2
 * - 2 * cos^2(a) * r1 * r2
 *
 *
 *
 *
 *
 * x1,x2 = (-(r1+r2)(1-cos(a)) +- sqrt((r1+r2)^2(1-cos(a))^2 + 4(1-cos^2(a))(r1*r2)))/2(1-cos(a))
 *
 *
 *
 *
 */