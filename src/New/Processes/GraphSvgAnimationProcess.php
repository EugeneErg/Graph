<?php

declare(strict_types=1);

namespace EugeneErg\Graph\New\Processes;

use EugeneErg\Collections\IntegerCollection;
use EugeneErg\Graph\New\Actions\AbstractAction;
use EugeneErg\Graph\New\Actions\CreateNewGraphAction;
use EugeneErg\Graph\New\Actions\MoveConnectedGraphAction;
use EugeneErg\Graph\New\Actions\MoveDisconnectedSubGraphAction;
use EugeneErg\Graph\New\Actions\SelectArticulationVertexesAction;
use EugeneErg\Graph\New\Animations\Collections\DataTransferObjectCollection;
use EugeneErg\Graph\New\Animations\Collections\LineCollection;
use EugeneErg\Graph\New\Animations\Collections\Point2DTrackCollection;
use EugeneErg\Graph\New\Animations\DataTransferObjects\DataTransferObjectInterface;
use EugeneErg\Graph\New\Animations\Effects\MoveObjectsAroundEffect;
use EugeneErg\Graph\New\Animations\Tracks\Point2DTrack;
use EugeneErg\Graph\New\Collections\ActionCollection;
use EugeneErg\Graph\New\Collections\AnimationGraphCollection;
use EugeneErg\Graph\New\Collections\AnimationVertexCollection;
use EugeneErg\Graph\New\DataTransferObjects\AnimationGraph;
use EugeneErg\Graph\New\DataTransferObjects\AnimationVertex;
use EugeneErg\Graph\New\Events\ArticulationVertexesFoundEvent;
use EugeneErg\Graph\New\Events\ConnectedGraphFoundEvent;
use EugeneErg\Graph\New\Events\DisconnectedGraphFoundEvent;
use EugeneErg\Graph\New\Services\CoordinateService;
use EugeneErg\Graph\New\Services\EventService;
use EugeneErg\Graph\New\Services\TreeService;
use EugeneErg\Graph\New\ValueObjects\Graph;

class GraphSvgAnimationProcess
{
    private array $levels = [];

    public function __construct(
        private readonly EventService $eventService,
        private readonly TreeService $treeService,
    ) {
    }

    public function generate(Graph $graph, int $vertexRadius = 20): DataTransferObjectCollection
    {
        $clearGraph = $graph->clone(false, false);
        $action = $this->getAction($clearGraph, $vertexRadius);
        $animationGraphs = new AnimationGraphCollection(immutable: false);
        $this->levels = [];
        $this->runAction(
            $action,
            new AnimationGraph(new AnimationVertexCollection(immutable: false)),
            $animationGraphs,
            $vertexRadius,
        );
        $forMerge = [];

        foreach ($animationGraphs as $animationGraph) {
            /** @var AnimationVertex $vertex */
            foreach ($animationGraph->vertexes as $vertex) {
                $forMerge[] = $vertex->connections;
            }
        }

        foreach ($animationGraphs as $animationGraph) {
            $forMerge[] = DataTransferObjectCollection::fromMap(
                fn (AnimationVertex $vertex): DataTransferObjectInterface => $vertex->circle,
                $animationGraph->vertexes,
            );
        }

        return DataTransferObjectCollection::fromMerge(...$forMerge)->unique();
    }

    private function getAction(Graph $graph, int $vertexRadius): CreateNewGraphAction
    {
        $result = new CreateNewGraphAction($graph->vertexes, $graph->connections, $vertexRadius);
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
        $moveConnectedGraphActions = new ActionCollection(immutable: false);
        $connectedGraphFoundListenerId = $this->eventService->listen(
            ConnectedGraphFoundEvent::class,
            function (ConnectedGraphFoundEvent $event)
            use ($moveConnectedGraphActions, $selectArticulationVertexesActions, $vertexRadius): void {
                $moveConnectedGraphActions[] = new MoveConnectedGraphAction(
                    $selectArticulationVertexesActions->last(),
                    $event->vertexes,
                    $vertexRadius,
                );
            }
        );
        $this->treeService->createFromGraph($graph);
        /*foreach ($this->graphService->splitGraphOnDisconnected($graph) as $subGraph) {
            $articulationVertexes = $this->articulationVertexesFinderService
                ->getArticulationVertexesInConnectedGraph($subGraph);
            $this->eventService->send(new ArticulationVertexesFoundEvent($articulationVertexes));
        }*/

        $this->eventService->dontListen([
            DisconnectedGraphFoundEvent::class => $disconnectedGraphFoundListenerId,
            ArticulationVertexesFoundEvent::class => $articulationVertexesFoundListenerId,
            ConnectedGraphFoundEvent::class => $connectedGraphFoundListenerId,
        ]);

        return $result;
    }

    private function runAction(
        AbstractAction $action,
        AnimationGraph $parentGraph,
        AnimationGraphCollection $graphs,
        int $vertexRadius,
    ): void {
        $steps = [[[$action, $parentGraph]]];
        $startMilliseconds = 0;

        do {
            $nextSteps = [];

            foreach ($steps as $parentGraphs) {
                $stepsMaxMilliseconds = $startMilliseconds;
                $graphsCount = $graphs->count();

                foreach ($parentGraphs as [$action, $parentGraph]) {
                    [$nextMilliseconds, $graph] = $action->drawGraph($parentGraph, $graphs, $startMilliseconds);
                    $stepsMaxMilliseconds = max($stepsMaxMilliseconds, $nextMilliseconds);

                    foreach ($action->children as $step => $child) {
                        $nextSteps[$step][] = [$child, $graph];
                    }
                }

                $startMilliseconds = $graphsCount === $graphs->count()
                    ? $stepsMaxMilliseconds
                    : $this->relaxGraphs($graphs, $stepsMaxMilliseconds, $vertexRadius);
            }

            $steps = $nextSteps;
        } while (!empty($steps));
    }

    private function relaxGraphs(AnimationGraphCollection $graphs, int $startMilliseconds, int $vertexRadius): int
    {
        if ($graphs->count() === 0) {
            return $startMilliseconds;
        }

        $radii = IntegerCollection::fromMap(
            fn (AnimationGraph $graph): int => CoordinateService::getRadius(
                $vertexRadius * 2,
                $graph->vertexes->count(),
            ) + $vertexRadius,
            $graphs,
        );
        $centers = CoordinateService::insertCircles($radii);

        foreach ($graphs as $graphNumber => $graph) {
            (new MoveObjectsAroundEffect(
                500,
                CoordinateService::getRadius($vertexRadius * 2, $graphs[$graphNumber]->vertexes->count()),
                $centers[$graphNumber],
                shiftAngle: CoordinateService::getAngle($graphs[$graphNumber]->vertexes->count()),
            ))->apply(
                Point2DTrackCollection::fromMap(
                    fn (AnimationVertex $vertex): Point2DTrack => $vertex->circle->center,
                    $graphs[$graphNumber]->vertexes,
                ),
                $startMilliseconds,
            );
        }

        return $startMilliseconds + 500;
    }
}
