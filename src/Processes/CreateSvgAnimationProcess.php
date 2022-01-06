<?php declare(strict_types=1);
namespace EugeneErg\Graph\Processes;

use EugeneErg\Graph\Collections\CssPropertyAnimationCube;
use EugeneErg\Graph\Collections\CssPropertyAnimationMatrix;
use EugeneErg\Graph\Collections\IntegerCollection;
use EugeneErg\Graph\Dto\CSS\CssPropertyAnimationDto;
use EugeneErg\Graph\Dto\Point2D;
use EugeneErg\Graph\Events\DisconnectedGraphFoundEvent;
use EugeneErg\Graph\Services\EventService;
use EugeneErg\Graph\Services\GraphService;
use EugeneErg\Graph\Services\ViewerService;
use EugeneErg\Graph\ValueObjects\AbstractGraph;
use EugeneErg\Graph\ValueObjects\ClearGraph;
use EugeneErg\Graph\ValueObjects\Slices\AbstractSlice;

final class CreateSvgAnimationProcess
{
    private AbstractGraph $mainGraph;
    private ?AbstractSlice $slice;
    private string $svgAnimation;
    private ClearGraph $mainClearGraph;
    private int $offset;
    private array $keyFrames;

    public function __construct(AbstractGraph $graph, ?AbstractSlice $slice = null, int $vertexRadius = 20)
    {
        $this->mainGraph = $graph;
        $this->slice = $slice;
        $this->offset = 0;
        $this->keyFrames = [];
        $this->svgAnimation = $this->createSvgAnimation($vertexRadius);
    }

    public function getSvgAnimation(): string
    {
        return $this->svgAnimation;
    }

    private function createSvgAnimation(int $vertexRadius): string
    {
        $this->mainClearGraph = ClearGraph::fromGraph($this->mainGraph);
        $animations = CssPropertyAnimationMatrix::fromFillKeysRecursive($this->mainClearGraph->vertexes, []);
        $graphRadius = $this->getRadius($vertexRadius * 2, $animations->count());
        $this->fromPointToCircleAnimation($animations, $graphRadius);
        $disconnectedGraphs = [];
        $disconnectedGraphFoundListenerId = EventService::instance()->listen(
            DisconnectedGraphFoundEvent::class,
            function (DisconnectedGraphFoundEvent $event) use (&$disconnectedGraphs): void {
                $disconnectedGraphs[] = $event->getVertexes()->values();
            }
        );
        GraphService::instance()->splitGraphOnDisconnected($this->mainClearGraph);
        EventService::instance()
            ->dontListen(DisconnectedGraphFoundEvent::class, $disconnectedGraphFoundListenerId);
        $count = count($disconnectedGraphs);

        if ($count < 3) {
            $centers = $this->getPoints($count, $graphRadius, -90);
            $imageRadius = $count < 2 ? $graphRadius : $graphRadius * 2;
        } else {
            $graphsRadius = $count < 8 ? $graphRadius * 2 : $this->getRadius($graphRadius, $count - 1);
            $centers = $this->getPoints(
                $count - 1,
                $graphsRadius,
                -90
            );
            $centers[] = new Point2D();
            $imageRadius = $graphRadius + $graphsRadius;
        }

        $mainVertexes = $this->mainClearGraph->vertexes;

        /** @var IntegerCollection $disconnectedGraph */
        foreach ($disconnectedGraphs as $number => $disconnectedGraph) {
            $this->paintAnimation($disconnectedGraph, $animations, '#7fff00');
            $disconnectedGraph = $mainVertexes->intersect($disconnectedGraph)->values();
            $points = $this->getPoints($disconnectedGraph->count(), $graphRadius, 0, $centers[$number]);
            $this->moveVertexes($disconnectedGraph, $points, $animations);
            $mainVertexes = $mainVertexes->difference($disconnectedGraph)->values();

            if (!$mainVertexes->isEmpty()) {
                $this->offset--;
                $points = $this->getPoints($mainVertexes->count(), $graphRadius);
                $this->moveVertexes($mainVertexes, $points, $animations);
            }

            $this->paintAnimation($disconnectedGraph, $animations, '#ffffff', true);
        }

        $connectionAnimations = new CssPropertyAnimationCube();

        foreach ($this->mainClearGraph->connections as $vertex1 => $connection) {
            foreach ($connection as $vertex2 => $value) {
                /** @var CssPropertyAnimationDto $animationDto */
                if ($vertex1 < $vertex2) {
                    foreach ($animations->getCollection($vertex1, true) ?? [] as $animationDto) {
                        if ($animationDto instanceof CssPropertyAnimationDto) {
                            $connectionAnimations->setItem(
                                $vertex1 . '-' . $vertex2,
                                'from',
                                null,
                                $animationDto
                            );
                        }
                    }
                    foreach ($animations->getCollection($vertex2, true) ?? [] as $animationDto) {
                        if ($animationDto instanceof CssPropertyAnimationDto) {
                            $connectionAnimations->setItem(
                                $vertex1 . '-' . $vertex2,
                                'to',
                                null,
                                $animationDto
                            );
                        }
                    }
                }
            }
        }

        return ViewerService::instance()->returnTemplate('svg-animation', [
            'keyFrames' => $this->keyFrames,
            'animations' => $animations,
            'vertexRadius' => $vertexRadius,
            'graphRadius' => $imageRadius,
            'connections' => $connectionAnimations,
        ]);








        //animation: moveToUp 5s 1s forwards;
        // moveToUp - название используемой анимайии
        // 5s - длительность анимации
        // 1s - начало анимации
        // forwards - итогове состояние

        /**
         * @keyframes moveToUp { - навзвание анимации
         *     to { - момент времени
         *         cy: -100 - состояние
         *     }
         * }
         */







        EventService::instance()
            ->dontListen(DisconnectedGraphFoundEvent::class, $disconnectedGraphFoundListenerId);
    }

    private function fromPointToCircleAnimation(CssPropertyAnimationMatrix $vertexes, int $graphRadius, ?Point2D $center = null): void
    {
        $center = $center ?? new Point2D();
        $vertexesCount = $vertexes->count();
        $points = $this->getPoints($vertexesCount, $graphRadius, 0, $center);
        $number = 0;
        $partPercent = 100 / $vertexesCount;
        $steps = ['0' => $center];

        foreach ($vertexes as $animations) {
            $percent = $partPercent * ($number + 1);
            $steps['100'] = $steps["{$percent}"] = $points[$number];
            $keyFrames = str_replace(
                '.',
                '_',
                "fromPointToCircleAnimation{$number}-{$vertexes->count()}-{$center->getX()}-{$center->getY()}-{$graphRadius}"
            );
            $animations[] = new CssPropertyAnimationDto($keyFrames, 2, $this->offset);
            $this->keyFrames[$keyFrames] = $steps;
            unset($steps['100']);
            $number++;
        }

        $this->offset += 2;
    }

    private function getPoints(int $count, int $graphRadius, float $startAngle = 0, ?Point2D $center = null): array
    {
        $center = $center ?? new Point2D();

        if ($count === 0) {
            return [];
        }

        if ($count === 1) {
            return [$center];
        }

        $delta = 2 * pi() / $count;
        $startAngle = $startAngle * pi() / 180;
        $result = [];

        for ($number = 0; $number < $count; $number++) {
            $angle = $startAngle + $number * $delta;
            $result[] = new Point2D(
                $center->getX() + $graphRadius * sin($angle),
                $center->getY() + $graphRadius * -cos($angle)
            );
        }

        return $result;
    }

    private function paintAnimation(
        IntegerCollection $vertexes,
        CssPropertyAnimationMatrix $animations,
        string $color,
        bool $fast = false
    ): void {
        $vertexCount = $vertexes->count();
        $keyFrames = str_replace('#', '', "paintAnimation{$vertexCount}-{$color}");
        $this->keyFrames[$keyFrames] = [
            100 => $color,
        ];

        foreach ($vertexes as $number => $vertex) {
            $animations->setItem($vertex, null, new CssPropertyAnimationDto(
                $keyFrames,
                $fast ? 1 : 1 / $vertexCount,
                $this->offset + ($fast ? 0 : $number / $vertexCount)
            ));
        }

        $this->offset++;
    }

    private function getRadius(int $subRadius, int $count): int
    {
        return (int) ceil($subRadius / sin(pi() / $count));
    }

    /* @param Point2D[] $coordinates */
    private function moveVertexes(
        IntegerCollection $vertexes,
        array $coordinates,
        CssPropertyAnimationMatrix $animations
    ): void {
        foreach ($vertexes as $number => $vertex) {
            $point = $coordinates[$number];
            $keyFrames = str_replace('.', '_', "moveVertexes{$point->getX()}-{$point->getY()}");
            $this->keyFrames[$keyFrames] = [
                100 => $point,
            ];
            $animations->setItem($vertex, null, new CssPropertyAnimationDto(
                $keyFrames,
                1,
                $this->offset
            ));
        }

        $this->offset++;
    }
}
