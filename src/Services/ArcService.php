<?php declare(strict_types = 1);
namespace EugeneErg\Graph\Services;

use EugeneErg\Graph\Collections\ArcCollection;
use EugeneErg\Graph\Collections\EdgeCollection;
use EugeneErg\Graph\Collections\IntegerCollection;
use EugeneErg\Graph\Collections\IntegerMatrix;
use EugeneErg\Graph\Collections\SolutionCollection;
use EugeneErg\Graph\Collections\SolutionMatrix;
use EugeneErg\Graph\Collections\SubGraphCollection;
use EugeneErg\Graph\Collections\TroubleCollection;
use EugeneErg\Graph\Collections\TroubleMatrix;
use EugeneErg\Graph\Events\EdgeFoundEvent;
use EugeneErg\Graph\Events\EmbeddedInTrouble;
use EugeneErg\Graph\Events\ReplacementFoundEvent;
use EugeneErg\Graph\ValueObjects\Arc;
use EugeneErg\Graph\ValueObjects\Edge;
use EugeneErg\Graph\ValueObjects\GravityVertex;
use EugeneErg\Graph\ValueObjects\Replacement;
use EugeneErg\Graph\ValueObjects\Solution;
use EugeneErg\Graph\ValueObjects\Temp\SubGraph;
use EugeneErg\Graph\ValueObjects\Trouble;
use LogicException;

class ArcService extends AbstractService
{
    public function createArcs(EdgeCollection $edges, Edge $outerEdge): ArcCollection
    {
        $arcs = new ArcCollection();
        $troubleVertexes = new TroubleCollection();
        $troubles = new TroubleMatrix();
        $graphs = new SubGraphCollection([new SubGraph($outerEdge, $edges)]);

        while (!$graphs->isEmpty()) {
            $graph = $graphs->shift();

            do {
                $found = 0;
                $nextEdges = new EdgeCollection();

                while (!$graph->edges->isEmpty()) {
                    $edge = $graph->edges->shift();
                    $replacement = $this->getReplacement($graph->counter, $edge);

                    if ($replacement === null) {
                        $nextEdges[] = $edge;

                        continue;
                    }

                    $found++;
                    $replaced = $graph->counter->getVertexes($replacement->start, $replacement->length);
                    $fromVertex = $replacement->firstVertex;
                    $toVertex = $replacement->lastVertex;
                    $prevCounter = $graph->counter;
                    $graph->counter = $graph->counter->replace(
                        $replacement->vertexes,
                        $replacement->start,
                        $replacement->length
                    );

                    if ($troubles->isEmpty()) {
                        EventService::instance()->send(new ReplacementFoundEvent($replacement));
                    } else {
                        $selectTroubles = new TroubleCollection();

                        foreach ($replaced as $vertex) {
                            if (isset($troubleVertexes[$vertex])) {
                                $selectTroubles[$troubleVertexes[$vertex]->fromVertex] = $troubleVertexes[$vertex];
                            }
                        }

                        $decisions = new SolutionMatrix();

                        foreach ($selectTroubles as $trouble) {
                            $decisionObject = new Solution($trouble, $fromVertex, $toVertex);
                            $decisions->setItem($decisionObject->type, null, $decisionObject);
                        }

                        if ($this->isCircle($decisions)) {
                            $graph->counter = $prevCounter;
                            $found--;
                            $nextEdges[] = $edge;

                            continue;
                        } elseif ($decisions->issetCollection(Solution::TYPE_EMBEDDING)) {
                            $trouble = $decisions->getItem(Solution::TYPE_EMBEDDING, 0)->trouble;

                            for ($i = 1; $i < $replaced->count() - 1; $i++) {
                                unset($troubleVertexes[$replaced[$i]]);
                            }

                            for ($i = 1; $i < $replacement->vertexes->count() - 1; $i++) {
                                $troubleVertexes[$replacement->vertexes[$i]] = $trouble;
                            }

                            $trouble->embedded($edge, $replacement);
                            EventService::instance()->send(new EmbeddedInTrouble($trouble, $edge, $replacement));

                            continue;
                        } elseif ($decisions->issetCollection(Solution::TYPE_ABSORPTION)) {
                            foreach ($decisions->getCollection(Solution::TYPE_ABSORPTION) as $decision) {
                                foreach ($decision->trouble->vertexes as $vertex) {
                                    unset($troubleVertexes[$vertex]);
                                }

                                $troubles->unsetItem($decision->trouble->fromVertex, $decision->trouble->toVertex);

                                if ($troubles->getCollection($decision->trouble->fromVertex)->isEmpty()) {
                                    $troubles->unsetCollection($decision->trouble->fromVertex);
                                }
                            }

                            $graphs = $graphs->merge($this->applySolution(
                                $decisions->getCollection(Solution::TYPE_ABSORPTION),
                                $replacement->vertexes,
                                $arcs,
                                $graph
                            ));

                            continue;
                        }
                    }

                    if ($replacement->length === 2 || $troubles->issetItem($fromVertex, $toVertex)) {
                        if ($troubles->issetItem($fromVertex, $toVertex)) {
                            for ($i = 1; $i < $replaced->count() - 1; $i++) {
                                unset($troubleVertexes[$replaced[$i]]);
                            }
                        } else {
                            $troubles->setItem($fromVertex, $toVertex, new Trouble(
                                $replaced,
                                $fromVertex,
                                $toVertex
                            ));
                        }

                        $troubles->getItem($fromVertex, $toVertex)->embedded($edge, $replacement);

                        for ($i = 1; $i < $replacement->vertexes->count() - 1; $i++) {
                            $troubleVertexes[$replacement->vertexes[$i]] = $troubles->getItem($fromVertex, $toVertex);
                        }
                    } else {
                        $arcs[] = new Arc(
                            GravityVertex::fromCollection($replaced->slice($replaced->count() >> 1, 1)),
                            new IntegerMatrix([$replacement->vertexes])
                        );
                    }
                }

                if ($found === 0) {
                    $decisions = new TroubleCollection();

                    foreach ($graph->counter->vertexes as $vertex) {
                        if (
                            isset($troubleVertexes[$vertex])
                            && !isset($decisions[$troubleVertexes[$vertex]->fromVertex])
                        ) {
                            $trouble = $troubleVertexes[$vertex];
                            $decisions[$trouble->fromVertex] = $trouble;
                            $troubles->unsetItem($trouble->fromVertex, $trouble->toVertex);

                            if ($troubles->getCollection($trouble->fromVertex)->isEmpty()) {
                                $troubles->unsetCollection($trouble->fromVertex);
                            }
                        }
                    }

                    if (!$decisions->isEmpty()) {
                        $mainGravityVertexes = new IntegerCollection();

                        foreach ($graph->counter->vertexes as $vertex) {
                            if (isset($troubleVertexes[$vertex])) {
                                unset($troubleVertexes[$vertex]);
                            } else {
                                $mainGravityVertexes[] = $vertex;
                            }
                        }

                        $graphs = $graphs->merge($this->solutionTroubles(
                            $decisions,
                            $arcs,
                            $mainGravityVertexes
                        ));
                    } elseif ($nextEdges->count() > 2) {
                        throw new LogicException();
                    }
                }

                $graph->edges = $graph->edges->merge($nextEdges);
            } while ($found !== 0);
        }

        return $arcs;
    }

    private function getReplacement(Edge $edgeA, Edge $edgeB): ?Replacement
    {
        $intersectA = $edgeA->vertexes->intersect($edgeB->vertexes);
        $intersectCount = $intersectA->count();

        if ($intersectCount < 2) {
            return null;
        }

        $edgeCountA = $edgeA->vertexes->count();
        $edgeCountB = $edgeB->vertexes->count();

        if (
            $edgeCountA === $intersectCount
            && $edgeCountB === $intersectCount
        ) {
            return null;
        }

        if ($edgeCountA === $intersectCount) {
            $intersectB = $edgeB->vertexes->intersect($intersectA);
            $shiftsAndDirection = $this->getShiftsAndDirection($intersectB, $edgeCountB, $edgeA, $edgeB);

            if ($shiftsAndDirection === null) {
                return null;
            }

            list($shiftA, $shiftB, $isRightDirection) = $shiftsAndDirection;

            if (!$isRightDirection) {
                $shiftA++;
                $shiftB = $edgeB->findVertex($edgeA->getVertex($shiftA));

                if ($shiftB === null) {
                    return null;
                }
            }
        } else {
            $shiftsAndDirection = $this->getShiftsAndDirection($intersectA, $edgeCountA, $edgeB, $edgeA);

            if ($shiftsAndDirection === null) {
                return null;
            }

            list($shiftB, $shiftA, $isRightDirection) = $shiftsAndDirection;
        }

        for ($i = 0; $i < $intersectCount; $i++) {
            $vertex = $edgeA->getVertex($shiftA + $i);

            if ($vertex !== $edgeB->getVertex($shiftB + ($isRightDirection ? $i : -$i))) {
                return null;
            }
        }

        return new Replacement(
            $edgeB->getVertexes(
                $shiftB,
                $isRightDirection ? $intersectCount - $edgeCountB - 2 : $edgeCountB - $intersectCount + 2
            ),
            $shiftA,
            $intersectCount
        );
    }

    private function getShift(IntegerCollection $vertexes, int $maxCount): ?int
    {
        /*$shift = reset($vertexes) === 0
            && end($vertexes) !== $maxCount;
        $prevValue = -1;
        $result = reset($vertexes);

        foreach ($vertexes as $value) {
            if ($value !== $prevValue + 1) {
                $result = $value;
                $shift++;
            }
            $prevValue = $value;
        }
        return $shift > 1 ? null : $result;*/
        $shift = isset($vertexes[0]) && !isset($vertexes[$maxCount - 1]);
        $prevKey = 0;
        $result = $vertexes->getKeyByPosition(0);

        foreach ($vertexes as $key => $value) {
            if ($key !== $prevKey) {
                $result = $key;

                if ($shift) {
                    return null;
                }

                $shift = true;
            }

            $prevKey = $key + 1;
        }

        return $result;
    }

    private function getShiftsAndDirection(IntegerCollection $intersect, int $edgeCount, Edge $edgeA, Edge $edgeB): ?array
    {
        $shiftB = $this->getShift($intersect, $edgeCount);

        if ($shiftB === null) {
            return null;
        }

        $shiftA = $edgeA->findVertex($edgeB->vertexes[$shiftB]);

        if ($shiftA === null) {
            return null;
        }

        $isRightDirection = $edgeA->getVertex($shiftA + 1) === $edgeB->getVertex($shiftB + 1);

        return [$shiftA, $shiftB, $isRightDirection];
    }

    private function applySolution(
        SolutionCollection $solutions,
        IntegerCollection $vertexes,
        ArcCollection $arcs,
        SubGraph $subGraph
    ): SubGraphCollection {
        $mainVertexes = new IntegerMatrix([clone $vertexes]);
        $count = 0;
        $mainGravityVertexes = new IntegerCollection();
        $graphs = new SubGraphCollection();
        $newArcs = [&$mainVertexes];

        foreach ($solutions as $solution) {
            $mainGravityVertexes[$solution->trouble->fromVertex] = $solution->trouble->fromVertex;
            $mainGravityVertexes[$solution->trouble->toVertex] = $solution->trouble->toVertex;

            if ($solution->fromPosition !== null && $solution->fromVertex !== $solution->trouble->fromVertex) {
                $tree = $solution->trouble->trees[$solution->fromVertex];
                $leftPart = $tree->findPath($solution->fromVertex, false);
                $innerEdges = $solution->trouble->getInnerEdges($solution->trouble->fromVertex, $solution->fromVertex);
                $subGraph->edges = $subGraph->edges->merge($innerEdges);
                $pos = $subGraph->counter->findVertex($solution->trouble->fromVertex);
                $pos2 = $subGraph->counter->findVertex($solution->fromVertex);
                $length = $subGraph->counter->getNormalVertexNumber($pos2 - $pos);
                $subGraph->counter = $subGraph->counter->replace($leftPart, $pos, $length);
                $pos = $solution->trouble->vertexes->search($solution->fromVertex, true);
                $newArcs[] = $leftArc = $solution->trouble->vertexes->slice($pos);
                $leftPart1 = clone $leftPart;
                $leftPart1[] = $mainVertexes->getCollection(0)->shift();
                $mainVertexes = (new IntegerMatrix([$leftPart1]))->merge($mainVertexes);
                $count++;
                $innerEdges = $solution->trouble->edges->difference($innerEdges);
                $graphs[] = new SubGraph(
                    new Edge($leftPart->merge($leftArc)),
                    $innerEdges
                );
            } elseif ($solution->toPosition !== null && $solution->toVertex !== $solution->trouble->toVertex) {
                $tree = $solution->trouble->trees[$solution->toVertex];
                $rightPath = $tree->findPath($solution->toVertex, true);
                $innerEdges = $solution->trouble->getInnerEdges($solution->toVertex, $solution->trouble->toVertex);
                $subGraph->edges = $subGraph->edges->merge($innerEdges);
                $pos = $subGraph->counter->findVertex($solution->toVertex);
                $pos2 = $subGraph->counter->findVertex($solution->trouble->toVertex);
                $length = $subGraph->counter->getNormalVertexNumber($pos2 - $pos);
                $subGraph->counter = $subGraph->counter->replace($rightPath, $pos + 1, $length);
                $pos = $solution->trouble->vertexes->search($solution->toVertex, true);
                $newArcs[] = $rightArc = $solution->trouble->vertexes->slice(0, $pos + 1);
                $rightPart1 = clone $rightPath;
                $rightPart1->unshift($mainVertexes->getCollection($mainVertexes->count() - 1)->pop());
                $mainVertexes = $mainVertexes->merge(new IntegerMatrix([$rightPart1]));

                $count++;
                $innerEdges = $solution->trouble->edges->difference($innerEdges);
                $graphs[] = new SubGraph(
                    new Edge($rightArc->merge($rightPath)),
                    $innerEdges
                );
            } else {
                $newArcs[] = $solution->trouble->vertexes;
                $graphs[] = new SubGraph(new Edge($solution->trouble->vertexes), $solution->trouble->edges);
            }
        }

        $mainGravityVertexes[$mainVertexes->getItem(0, 0)] = $mainVertexes->getItem(0, 0);
        $last = $mainVertexes->getValueByPosition()->getValueByPosition();
        $mainGravityVertexes[$last] = $last;

        foreach ($newArcs as $arc) {
            $arcs[] = new Arc(
                GravityVertex::fromValues($mainGravityVertexes),
                $arc instanceOf IntegerMatrix
                    ? $arc->filter(fn (IntegerCollection $value): bool => !$value->isEmpty())->values()
                    : new IntegerMatrix([$arc])
            );
        }

        return $graphs;
    }

    private function solutionTroubles(
        TroubleCollection $troubles,
        ArcCollection $arcs,
        IntegerCollection $mainGravityVertexes
    ): SubGraphCollection {
        return SubGraphCollection::fromMap(function (Trouble $trouble) use ($arcs, $mainGravityVertexes): SubGraph {
            $arcs[] = new Arc(
                GravityVertex::fromCollection($mainGravityVertexes),
                new IntegerMatrix([$trouble->vertexes])
            );

            return new SubGraph(new Edge($trouble->vertexes), $trouble->edges);
        }, false, $troubles);
    }

    private function isCircle(SolutionMatrix $decisions): bool
    {
        if ($decisions->isEmpty()) {
            return false;
        }

        if ($decisions->issetCollection(Solution::TYPE_CIRCLE)) {
            return true;
        }

        $from = null;
        $to = null;

        foreach ($decisions->getCollection(Solution::TYPE_ABSORPTION, true) ?? [] as $decision) {
            if ($decision->fromPosition !== null) {
                $from = $decision;
            } elseif ($decision->toPosition !== null) {
                $to = $decision;
            }
        }

        return $from !== null && $to !== null && $from->trouble->fromVertex === $to->trouble->toVertex;
    }
}
