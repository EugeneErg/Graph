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
        $graphRadius = (int) ceil($vertexRadius * 2 / sin(pi() / $animations->count()));
        $this->fromPointToCircleAnimation($animations, $graphRadius);
        $disconnectedGraphs = [];
        $disconnectedGraphFoundListenerId = EventService::instance()->listen(
            DisconnectedGraphFoundEvent::class,
            function (DisconnectedGraphFoundEvent $event) use (&$disconnectedGraphs): void {
                $disconnectedGraphs[] = $event->getVertexes();
            }
        );
        GraphService::instance()->splitGraphOnDisconnected($this->mainClearGraph);
        EventService::instance()
            ->dontListen(DisconnectedGraphFoundEvent::class, $disconnectedGraphFoundListenerId);

        $count = count($disconnectedGraphs);

        if ($count === 2) {
            $centers = $this->getPoints(count($disconnectedGraphs), $graphRadius, -90);
        } else {
            $centers = $this->getPoints(count($disconnectedGraphs) - 1, $graphRadius * 2, -90);
            array_unshift($centers, new Point2D());
        }

        foreach ($disconnectedGraphs as $disconnectedGraph) {
            $this->paintAnimation($disconnectedGraph);
        }

        $connectionAnimations = new CssPropertyAnimationCube();

        foreach ($this->mainClearGraph->connections as $vertex1 => $connection) {
            foreach ($connection as $vertex2 => $value) {
                /** @var CssPropertyAnimationDto $animationDto */
                if ($vertex1 < $vertex2) {
                    foreach ($animations->getCollection($vertex1, true) ?? [] as $animationDto) {
                        $connectionAnimations->setItem(
                            $vertex1 . '-' . $vertex2,
                            'from',
                            null,
                            $animationDto
                        );
                    }
                    foreach ($animations->getCollection($vertex2, true) ?? [] as $animationDto) {
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

        return ViewerService::instance()->returnTemplate('svg-animation', [
            'keyFrames' => $this->keyFrames,
            'animations' => $animations,
            'vertexRadius' => $vertexRadius,
            'graphRadius' => $graphRadius,
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

    private function paintAnimation(IntegerCollection $vertexes): void
    {

    }
}
