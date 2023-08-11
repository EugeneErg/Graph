<?php

declare(strict_types=1);

namespace EugeneErg\Graph\New\Actions;

use EugeneErg\Collections\IntegerCollection;
use EugeneErg\Graph\New\Animations\Collections\ColorTrackCollection;
use EugeneErg\Graph\New\Animations\Effects\BrushObjectsEffect;
use EugeneErg\Graph\New\Animations\Tracks\ColorTrack;
use EugeneErg\Graph\New\Collections\ActionCollection;
use EugeneErg\Graph\New\Collections\AnimationGraphCollection;
use EugeneErg\Graph\New\DataTransferObjects\AnimationGraph;
use EugeneErg\Graph\New\DataTransferObjects\AnimationVertex;

class SelectArticulationVertexesAction extends AbstractAction
{
    public function __construct(
        MoveDisconnectedSubGraphAction $parent,
        public readonly IntegerCollection $vertexes,
        public readonly int $vertexRadius,
    ) {
        parent::__construct($parent);
    }

    public function drawGraph(
        AnimationGraph $parentGraph,
        AnimationGraphCollection $graphs,
        int $startMilliseconds,
    ): array {
        $startMilliseconds = (new BrushObjectsEffect('#fff', 500))->apply(
            ColorTrackCollection::fromMap(
                fn (AnimationVertex $vertex): ColorTrack => $vertex->circle->color,
                $parentGraph->vertexes,
            ),
            $startMilliseconds,
        );
        $colors = new ColorTrackCollection(immutable: false);

        foreach ($this->vertexes as $vertex) {
            $colors[] = $parentGraph->vertexes[$vertex]->circle->color;
        }

        $startMilliseconds = (new BrushObjectsEffect('#0ff', 500))->apply(
            $colors,
            $startMilliseconds,
        );
        $this->sortChildren();

        return [$startMilliseconds, $parentGraph];
    }

    private function sortChildren(): void
    {
        if ($this->children->isEmpty()) {
            return;
        }

        $actionVertexes = [];
        $vertexActions = [];

        /** @var MoveConnectedGraphAction $action */
        foreach ($this->children as $number => $action) {
            $intersectionVertexes = IntegerCollection::fromIntersect(
                true,
                false,
                $this->vertexes,
                $action->vertexes,
            );

            foreach ($intersectionVertexes as $vertex) {
                $vertexActions[$vertex][$number] = $action;
                $actionVertexes[$number][$vertex] = $action;
            }
        }

        $last = $this->getChildWithMaxVertexCount();
        $result = new ActionCollection([$last], immutable: false);
        $lastPos = $this->children->search($last);
        $resultVertexes = new IntegerCollection(array_keys($actionVertexes[$lastPos]), false);

        foreach ($actionVertexes[$lastPos] as $vertex => $action) {
            unset($vertexActions[$vertex][$lastPos]);
        }

        unset($actionVertexes[$lastPos]);

        for ($i = 0; $i < $resultVertexes->count(); $i++) {
            $vertex = $resultVertexes[$i];

            if (!isset($vertexActions[$vertex])) {
                continue;
            }

            $result->push(new ActionCollection($vertexActions[$vertex]));

            foreach ($vertexActions[$vertex] as $number => $actionA) {
                foreach ($actionVertexes[$number] as $vertexB => $actionB) {
                    $resultVertexes[] = $vertexB;
                    unset($vertexActions[$vertexB][$number]);
                }

                unset($actionVertexes[$number]);
            }

            unset($vertexActions[$vertex]);
        }

        $this->children->splice();
        $this->children->push($result->reverse());
    }

    public function getChildWithMaxVertexCount(): MoveConnectedGraphAction
    {
        return $this->children->reduce(
            fn (?MoveConnectedGraphAction $result, MoveConnectedGraphAction $next): MoveConnectedGraphAction =>
                $result === null || $result->vertexes->count() < $next->vertexes->count() ? $next : $result,
        );
    }
}
