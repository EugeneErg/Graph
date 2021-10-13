<?php declare(strict_types = 1);
namespace EugeneErg\Graph\Services;

use EugeneErg\Graph\ValueObjects\Arc;
use EugeneErg\Graph\ValueObjects\Edge;
use EugeneErg\Graph\ValueObjects\GravityVertex;
use EugeneErg\Graph\ValueObjects\Solution;
use EugeneErg\Graph\ValueObjects\Temp\SubGraph;

class TroubleService extends AbstractService
{
    /**
     * @param Solution[] $solutions
     * @param int[] $vertexes
     * @param Arc[] $arcs
     * @param SubGraph $subGraph
     * @return SubGraph[]
     */
    public function applySolution(array $solutions, array $vertexes, array &$arcs, SubGraph $subGraph): array
    {
        $mainVertexes = $vertexes;
        $count = 0;
        $mainGravityVertexes = [
            $vertexes[0] => $vertexes[0],
            end($vertexes) => end($vertexes),
        ];
        $graphs = [];
        $newArcs = [&$mainVertexes];

        foreach ($solutions as $pos => $solution) {
            $mainGravityVertexes[$solution->trouble->fromVertex] = $solution->trouble->fromVertex;
            $mainGravityVertexes[$solution->trouble->toVertex] = $solution->trouble->toVertex;

            if ($solution->fromPosition !== null) {
                $tree = $solution->trouble->trees[$solution->fromVertex];
                $leftPart = $tree->findPath($solution->fromVertex, false);
                $innerEdges = $solution->trouble->getInnerEdges($solution->trouble->fromVertex, $solution->fromVertex);
                $subGraph->edges = array_merge($subGraph->edges, $innerEdges);
                $subGraph->counter =
                $pos = array_search($solution->fromVertex, $solution->trouble->vertexes);
                $newArcs[] = $leftArc = array_slice($solution->trouble->vertexes, $pos);
                $mainVertexes = $count === 0 ? [$leftPart, $mainVertexes] : array_merge([$leftPart], $mainVertexes);
                $count++;
                $innerEdges = array_diff($solution->trouble->edges, $innerEdges);
                $graphs[] = new SubGraph(
                    new Edge(array_merge($leftPart, array_slice($leftArc, 1))),
                    $innerEdges
                );
            } elseif ($solution->toPosition !== null) {
                $tree = $solution->trouble->trees[$solution->toVertex];
                $rightPath = $tree->findPath($solution->toVertex, true);
                $innerEdges = $solution->trouble->getInnerEdges($solution->toVertex, $solution->trouble->toVertex);
                $subGraph->edges = array_merge($subGraph->edges, $innerEdges);
                $pos = array_search($solution->fromVertex, $solution->trouble->vertexes);
                $newArcs[] = $rightArc = array_slice($solution->trouble->vertexes, 0, $pos + 1);
                $mainVertexes = $count === 0 ? [$mainVertexes, $rightPath] : array_merge($mainVertexes, [$rightPath]);
                $count++;
                $innerEdges = array_diff($solution->trouble->edges, $innerEdges);
                $graphs[] = new SubGraph(
                    new Edge(array_merge(array_slice($rightArc, 0, -1), $rightPath)),
                    $innerEdges
                );
            } else {
                $newArcs[] = $solution->trouble->vertexes;
                $graphs[] = new SubGraph(new Edge($solution->trouble->vertexes), $solution->trouble->edges);
            }
        }

        foreach ($newArcs as $arc) {
            $arcs[] = new Arc($arc, new GravityVertex(...$mainGravityVertexes));
        }

        return $graphs;
    }
}
