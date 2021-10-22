<?php declare(strict_types = 1);
namespace EugeneErg\Graph\Services;

use EugeneErg\Graph\Collections\IntegerCollection;
use EugeneErg\Graph\ValueObjects\Canvas;
use EugeneErg\Graph\ValueObjects\ClearGraph;
use EugeneErg\Graph\ValueObjects\Tree;

class TreeService extends AbstractService
{
    /** @var int[] */
    private $articulationVertex;
    /** @var Canvas */
    private $canvas;
    /** @var array */
    private $result;

    public function fromConnectionGraph(ClearGraph $graph): Tree
    {
        $this->articulationVertex = ArticulationVertexesFinderService::instance()
            ->getArticulationVertexesInConnectedGraph($graph);

        if (!count($this->articulationVertex)) {
            return new Tree($graph);
        }

        $this->canvas = new Canvas($graph);
        $this->result = [];
        $this->split();
        $branches = $connections = [];

        foreach ($this->result as $number => $vertexes) {
            $branches[] = $graph->createSupGraph(...$vertexes);

            foreach ($vertexes as $vertex) {
                $connections[$vertex][] = $number;
            }
        }

        return new Tree($graph, $branches, $connections);
    }

    private function split(int $maxColor = 0): bool
    {
        $color = $maxColor;
        $result = false;

        foreach ($this->articulationVertex as $vertexA) {
            if ($this->canvas->getColor($vertexA) !== $maxColor) {
                continue;
            }

            unset($this->articulationVertex[$vertexA]);

            foreach ($this->canvas->graph->getRow($vertexA) as $vertexB => $value) {
                if ($this->canvas->getColor($vertexB) !== $maxColor) {
                    continue;
                }

                $result = true;
                CanvasService::instance()->pixels($this->canvas, new IntegerCollection([$vertexA]), ++$color);
                $vertexes = CanvasService::instance()->fill($this->canvas, $vertexB, $color);
                $vertexes[$vertexA] = $vertexA;

                if (!$this->split($color)) {
                    $this->result[] = $vertexes;
                }
            }
        }

        return $result;
    }
}
