<?php declare(strict_types = 1);
namespace EugeneErg\Graph\Processes;

use EugeneErg\Graph\Collections\AngleCollection;
use EugeneErg\Graph\Collections\Collection;
use EugeneErg\Graph\Collections\IntegerCollection;
use EugeneErg\Graph\Dto\Point2D;
use EugeneErg\Graph\Events\ArticulationVertexesFoundEvent;
use EugeneErg\Graph\Events\ConnectedGraphFoundEvent;
use EugeneErg\Graph\Events\DisconnectedGraphFoundEvent;
use EugeneErg\Graph\Processes\Actions\CreateNewGraphAction;
use EugeneErg\Graph\Processes\Actions\MoveConnectedGraphAction;
use EugeneErg\Graph\Processes\Actions\MoveDisconnectedSubGraphAction;
use EugeneErg\Graph\Processes\Actions\SelectArticulationVertexesAction;
use EugeneErg\Graph\Processes\Collections\ActionCollection;
use EugeneErg\Graph\Processes\Collections\ConnectionsCollection;
use EugeneErg\Graph\Processes\Collections\ConnectionsMatrix;
use EugeneErg\Graph\Processes\Collections\GraphCollection;
use EugeneErg\Graph\Processes\Collections\VertexesCollection;
use EugeneErg\Graph\Processes\SvgAnimation\ValueObject\Connection;
use EugeneErg\Graph\Processes\SvgAnimation\ValueObject\Graph;
use EugeneErg\Graph\Processes\SvgAnimation\ValueObject\Vertex;
use EugeneErg\Graph\Processes\SvgAnimation\ValueObject\State;
use EugeneErg\Graph\Services\CoordinateService;
use EugeneErg\Graph\Services\EventService;
use EugeneErg\Graph\Services\TreeService;
use EugeneErg\Graph\Services\ViewerService;
use EugeneErg\Graph\ValueObjects\AbstractGraph;
use EugeneErg\Graph\ValueObjects\Angle;
use EugeneErg\Graph\ValueObjects\ClearGraph;
use EugeneErg\Graph\ValueObjects\Slices\AbstractSlice;

class NewCreateSvgAnimationProcess
{
    private AbstractGraph $graph;
    private ?AbstractSlice $slice;
    private string $svgAnimation;
    private CreateNewGraphAction $createNewGraphAction;
    private MoveDisconnectedSubGraphAction $moveDisconnectedSubGraphAction;
    private SelectArticulationVertexesAction $selectArticulationVertexesAction;
    private MoveConnectedGraphAction $moveConnectedGraphAction;

    public function __construct(AbstractGraph $graph, ?AbstractSlice $slice = null, int $vertexRadius = 20)
    {
        $this->graph = $graph;
        $this->slice = $slice;
        $this->svgAnimation = $this->createSvgAnimation($vertexRadius);
    }

    public function getSvgAnimation(): string
    {
        return $this->svgAnimation;
    }

    private function createSvgAnimation(int $vertexRadius): string
    {
        $clearGraph = ClearGraph::fromGraph($this->graph);
        $actions = $this->getActions($clearGraph);
        $graphs = $this->createNewGraph($actions, $vertexRadius, $minPoint, $maxPoint);
        $vertexes = new Collection();
        $connections = new Collection();

        foreach ($graphs as $graph) {
            $vertexes[] = $graph->getVertexes();
            $connections[] = $graph->getConnections();
        }

        return ViewerService::instance()->returnTemplate('svg-animation2', [
            'vertexes' => VertexesCollection::fromMerge(...$vertexes),
            'connections' => ConnectionsCollection::fromMerge(...$connections),
            'vertexRadius' => $vertexRadius,
            'minPoint' => $minPoint,
            'maxPoint' => $maxPoint,
        ]);
    }

    private function getActions(ClearGraph $clearGraph): CreateNewGraphAction
    {
        $this->createNewGraphAction = new CreateNewGraphAction($clearGraph->vertexes, $clearGraph->connections);
        $moveDisconnectedSubGraphActions = [];
        $disconnectedGraphFoundListenerId = EventService::instance()->listen(
            DisconnectedGraphFoundEvent::class,
            function (DisconnectedGraphFoundEvent $event) use (&$moveDisconnectedSubGraphActions): void {
                $moveDisconnectedSubGraphActions[] = new MoveDisconnectedSubGraphAction(
                    $event->getVertexes(),
                    $this->createNewGraphAction
                );
            }
        );
        $selectArticulationVertexesActions = [];
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
        TreeService::instance()->createFromGraph($clearGraph);
        EventService::instance()->dontListen([
            DisconnectedGraphFoundEvent::class => $disconnectedGraphFoundListenerId,
            ArticulationVertexesFoundEvent::class => $articulationVertexesFoundListenerId,
            ConnectedGraphFoundEvent::class => $connectedGraphFoundListenerId,
        ]);

        return $this->createNewGraphAction;
    }

    private function getRadius(int $subRadius, int $count): int
    {
        return (int) ceil($subRadius / sin(pi() / $count));
    }

    private function createNewGraph(
        CreateNewGraphAction $createNewGraphAction,
        int $vertexRadius,
        ?Point2D &$minPoint,
        ?Point2D &$maxPoint
    ): GraphCollection {
        if ($minPoint === null) {
            $minPoint = new Point2D();
        }

        if ($maxPoint === null) {
            $maxPoint = new Point2D();
        }

        $vertexesCount = $createNewGraphAction->getVertexes()->count();
        $angle = CoordinateService::instance()->getAngle($vertexesCount);
        $graphRadius = CoordinateService::instance()
            ->getRadius($vertexRadius * 2, $vertexesCount);
        $vertexState = new State(0, 0, null, new Point2D());
        $number = 0;
        $vertexes = VertexesCollection::fromWalk(
            $createNewGraphAction->getVertexes(),
            function (int $vertex, int $number)
            use (&$vertexState, $vertexesCount, &$number, $angle, $graphRadius, &$minPoint, &$maxPoint): Vertex {
                $coordinate = CoordinateService::instance()
                    ->getPoint($graphRadius, CoordinateService::instance()->getFinalAngle($angle, $number));
                $maxPoint = Point2D::max($coordinate, $maxPoint);
                $minPoint = Point2D::min($coordinate, $minPoint);
                $vertexState = new State(
                    100,
                    0,
                    $vertexState,
                    $coordinate
                );

                if ($vertexesCount !== $number + 1) {
                    $vertexStateWithFinal = new State(
                        100 * ($vertexesCount - $number - 1),
                        0,
                        $vertexState,
                        $coordinate
                    );
                }

                return new Vertex($vertex, $vertexStateWithFinal ?? $vertexState);
            }
        )->combineKeys($createNewGraphAction->getVertexes());
        $connectionsMatrix = new ConnectionsMatrix();

        foreach ($createNewGraphAction->getVertexes() as $vertexA => $connection) {
            foreach ($connection as $vertexB => $value) {
                if ($vertexA < $vertexB) {
                    $newConnection = new Connection($vertexes[$vertexA], $vertexes[$vertexB]);
                    $connectionsMatrix->setItem($vertexA, $vertexB, $newConnection);
                    $connectionsMatrix->setItem($vertexB, $vertexA, $newConnection);
                }
            }
        }

        $graph = new Graph($vertexes, $connectionsMatrix, $graphRadius);

        return $this->moveDisconnectedSubGraphs(
            $graph,
            $createNewGraphAction->getChildren(),
            $vertexRadius,
            $createNewGraphAction->getVertexes(),
            $minPoint,
            $maxPoint
        );
    }

    /** @param MoveDisconnectedSubGraphAction[]|ActionCollection $actions */
    private function moveDisconnectedSubGraphs(
        Graph $graph,
        ActionCollection $actions,
        int $vertexRadius,
        IntegerCollection $mainVertexes,
        Point2D &$minPoint,
        Point2D &$maxPoint
    ): GraphCollection {
        $big = $this->getNumberActionWithMaximumVertexCount($actions);
        $bigAction = $actions[$big];
        unset($actions[$big]);
        $actionsCount = $actions->count() + $bigAction->getChildren()->count();
        $startAngle = new Angle($actionsCount < 3 ? -90 : 0);
        $delta = $graph->getOccupiedAngle();
        $radii = new IntegerCollection();
        $angles = new AngleCollection();

        foreach ($actions as $action) {
            $graphRadius = $actionsCount < 8
                ? $graph->getRadius()
                : CoordinateService::instance()->getRadius(
                    $vertexRadius * 2,
                    $action->getVertexes()->count()
                );
            $angle = $actionsCount === 1
                ? $graph->getOccupiedAngle()
                : CoordinateService::instance()->findAnOccupiedAngle(
                    $actionsCount < 4 ? 0 : $graph->getRadius(),
                    $graphRadius
                );
            $angles[] = $angle;
            $delta = $delta->minus($angle);
            $radii[] = $graphRadius;
        }

        for ($number = 0; $number < $bigAction->getChildren()->count() - 1; $number++) {
            $action = $bigAction->getChild($number);
            $graphRadius = $actionsCount < 8
                ? $graph->getRadius()
                : CoordinateService::instance()->getRadius(
                    $vertexRadius * 2,
                    $action->getVertexes()->count()
                );
            $delta = $delta->minus(CoordinateService::instance()->findAnOccupiedAngle(
                $actionsCount < 4 ? 0 : $graph->getRadius(),
                $graphRadius
            ));
        }

        $actions[] = $bigAction;
        $delta = $delta->divided($actionsCount);
        $fullAngle = $startAngle;
        $parentVertexes = $graph->getVertexes();
        $number = 0;

        return GraphCollection::fromMerge(
            false,
            ...Collection::fromMap(
                function (
                    MoveDisconnectedSubGraphAction $moveDisconnectedSubGraphAction,
                    int $graphRadius,
                    Angle $occupiedAngle
                ) use (
                    $actionsCount,
                    $parentVertexes,
                    &$mainVertexes,
                    $graph,
                    $vertexRadius,
                    $delta,
                    &$fullAngle,
                    &$number,
                    &$minPoint,
                    &$maxPoint
                ): GraphCollection {
                    $distance = ($actionsCount === 1 ? 0 : $graph->getRadius()) + $graphRadius;
                    $center =
                        $actionsCount === 3 && $number === 2
                        ? new Point2D()
                        : $this->getCenter($distance, $fullAngle, $occupiedAngle);
                    $count = $moveDisconnectedSubGraphAction->getVertexes()->count();
                    $delays = [];

                    foreach ($moveDisconnectedSubGraphAction->getVertexes() as $number => $vertex) {
                        $delays[$vertex] = ($count - $number - 1) * 100;
                        $parentVertexes[$vertex]->brush('#7fff00', 100, $number * 100);
                    }

                    $disconnectedGraph = $mainVertexes->intersect($moveDisconnectedSubGraphAction->getVertexes())->values();
                    $vertexes = VertexesCollection::fromMap(
                        function (int $vertex, Point2D $coordinate)
                        use ($parentVertexes, $delays, &$maxPoint, &$minPoint): Vertex {
                            $maxPoint = Point2D::max($coordinate, $maxPoint);
                            $minPoint = Point2D::min($coordinate, $minPoint);
                            $result = $parentVertexes[$vertex];
                            unset($parentVertexes[$vertex]);
                            $result->move($coordinate, 1000, $delays[$vertex]);

                            return $result;
                        },
                        false,
                        $disconnectedGraph,
                        CoordinateService::instance()->getPoints(
                            $moveDisconnectedSubGraphAction->getVertexes()->count(),
                            $distance,
                            $center
                        )
                    )->combineKeys($disconnectedGraph);
                    VertexesCollection::fromMap(function (Vertex $vertex, Point2D $coordinate) use ($count): Vertex {
                        $vertex->move($coordinate, 1000, 100 * $count);

                        return $vertex;
                    }, false, $parentVertexes, CoordinateService::instance()->getPoints(
                        $mainVertexes->count(),
                        $graph->getRadius(),
                        $center
                    ));
                    $mainVertexes = $mainVertexes->difference($disconnectedGraph)->values();
                    $graph = new Graph(
                        $vertexes,
                        $graph->getSubMatrix($vertexes),
                        $distance + $graphRadius,
                        $fullAngle,
                        $occupiedAngle,
                        $center
                    );
                    $fullAngle = $fullAngle->plus($occupiedAngle)->plus($delta);
                    /** @var SelectArticulationVertexesAction $selectArticulationVertexesAction */
                    $selectArticulationVertexesAction = $moveDisconnectedSubGraphAction->getChild(0);
                    $notArticulationVertexes = $vertexes->difference($selectArticulationVertexesAction->getVertexes());

                    /** @var Vertex $vertex */
                    foreach ($notArticulationVertexes as $vertex) {
                        $vertex->brush('#ffffff', 1);
                    }

                    return $this->moveConnectedSubGraphs(
                        $graph,
                        $selectArticulationVertexesAction->getChildren(),
                        $selectArticulationVertexesAction->getVertexes(),
                        $vertexRadius,
                        $minPoint,
                        $maxPoint
                    );
                },
                false,
                $actions,
                $radii,
                $angles
            )
        );
    }

    /** @param ActionCollection|MoveConnectedGraphAction[] $actions */
    private function moveConnectedSubGraphs(
        Graph $graph,
        ActionCollection $actions,
        IntegerCollection $articulationVertexes,
        int $vertexRadius,
        Point2D &$minPoint,
        Point2D &$maxPoint
    ): GraphCollection {
        $result = new GraphCollection([$graph]);
        $actionsCount = $actions->count();

        if ($actionsCount === 1) {
            return $result;
        }

        $big = $this->getNumberActionWithMaximumVertexCount($actions);
        $bigAction = $actions[$big];
        $actions = $this->directed($big, $actions, $articulationVertexes);
        $delta = $graph->getOccupiedAngle();
        $radii = new IntegerCollection();
        $angles = new AngleCollection();

        foreach ($actions as $action) {
            $graphRadius = CoordinateService::instance()->getRadius(
                $vertexRadius * 2,
                $action->getVertexes()->count()
            );
            $angle = CoordinateService::instance()->findAnOccupiedAngle(
                $graph->getRadius(),
                $graphRadius
            );
            $angles[] = $angle;
            $delta = $delta->minus($angle);
            $radii[] = $graphRadius;
        }

        $actions[] = $bigAction;
        $fullAngle = $delta->divided(2)->plus($graph->getStartOccupiedAngle());
        $parentVertexes = $graph->getVertexes();

        return $result->merge(GraphCollection::fromMap(
            function (MoveConnectedGraphAction $action, Angle $angle, int $radius)
            use (&$fullAngle, $articulationVertexes, $graph, $parentVertexes, $result, &$minPoint, &$maxPoint): Graph {
                $count = $action->getVertexes()->count();
                $delays = [];

                foreach ($action->getVertexes() as $number => $vertex) {
                    $delays[$vertex] = ($count - $number - 1) * 100;
                    $parentVertexes[$vertex]->brush('#7fff00', 100, $number * 100);
                }

                $connectedGraph = IntegerCollection::fromKeys($parentVertexes)
                    ->intersect($action->getVertexes())->values();
                $articulationVertex = $action->getVertexes()
                    ->intersect($articulationVertexes)->getValueByPosition(0);
                $distance = $graph->getRadius() + $radius;
                $center = $this->getCenter($distance, $fullAngle, $angle);
                $vertexes = VertexesCollection::fromMap(
                    function (int $vertex, Point2D $coordinate)
                    use ($parentVertexes, $delays, $articulationVertex, &$minPoint, &$maxPoint): Vertex {
                        $minPoint = Point2D::min($coordinate, $minPoint);
                        $maxPoint = Point2D::max($coordinate, $maxPoint);

                        if ($vertex === $articulationVertex) {
                            $result = clone $parentVertexes[$vertex];
                        } else {
                            $result = $parentVertexes[$vertex];

                            unset($parentVertexes[$vertex]);
                        }

                        $result->move($coordinate, 1000, $delays[$vertex]);

                        return $result;
                    },
                    false,
                    $connectedGraph,
                    CoordinateService::instance()->getPoints(
                        $action->getVertexes()->count(),
                        $radius,
                        $center
                    )
                )->combineKeys($connectedGraph);
                $connectionsMatrix = new ConnectionsMatrix();

                foreach ($action->getVertexes() as $vertexA => $connection) {
                    foreach ($connection as $vertexB => $value) {
                        if ($vertexA < $vertexB) {
                            $newConnection = new Connection($vertexes[$vertexA], $vertexes[$vertexB]);
                            $connectionsMatrix->setItem($vertexA, $vertexB, $newConnection);
                            $connectionsMatrix->setItem($vertexB, $vertexA, $newConnection);
                        }
                    }
                }

                return new Graph($vertexes, $connectionsMatrix, $radius);

                //поворачиваем основной граф так,
                //чтобы артикуляционная вершина была направлена в сторону нового подграфа
                //одновременно часть вершин переносится на новую позицию,
                //артикуляционная вершина находится со стороны основного графа
                //артикуляционная вершина идет до середины между основным графом и новым подграфом
                //затем разделяется на две, новая идет в сторону подграфа, старая в сторону основного графа
            },
            false,
            $actions,
            $angles,
            $radii
        ));
    }

    public function getNumberActionWithMaximumVertexCount(ActionCollection $actions): ?int
    {
        $result = null;

        foreach ($actions as $number => $action) {
            if ($result === null || $action->getVertexes()->count() > $actions[$result]->getVertexes()->count()) {
                $result = $number;
            }
        }

        return $result;
    }

    private function getCenter(int $distance, Angle $fullAngle, Angle $angle): Point2D
    {
        return CoordinateService::instance()
            ->getPoint($distance, $angle->divided(2)->plus($fullAngle));
    }

    /** @param ActionCollection|MoveConnectedGraphAction[] $actions */
    private function directed(
        int $lastActionNumber,
        ActionCollection $actions,
        IntegerCollection $articulationVertexes
    ): ActionCollection {
        $actionVertexes = [];
        $vertexActions = [];

        foreach ($actions as $number => $action) {
            $intersectionVertexes = $articulationVertexes->intersect($action->getVertexes());

            foreach ($intersectionVertexes as $vertex) {
                $vertexActions[$vertex][$number] = $action;
                $actionVertexes[$number][$vertex] = $action;
            }
        }

        $result = new ActionCollection();

        while (count($vertexActions) > 1) {
            $exists = false;

            foreach ($actionVertexes as $numberA => $vertexAction) {
                if ($numberA === $lastActionNumber || count($vertexAction) !== 1) {
                    continue;
                }

                $exists = true;
                $result[] = $actions[$numberA];
                reset($vertexAction);
                $vertex = key($vertexAction);

                foreach ($vertexActions[$vertex] as $numberB => $action) {
                    unset($vertexActions[$vertex][$numberB], $actionVertexes[$numberB][$vertex]);

                    if (count($vertexActions[$vertex]) === 0) {
                        unset($vertexActions[$vertex]);
                    }

                    if (count($actionVertexes[$numberB]) === 0) {
                        unset($actionVertexes[$numberB]);
                    }
                }
            }

            if (!$exists) {
                throw new \LogicException('incorrect action list');
            }
        }

        return $result;
    }
}
