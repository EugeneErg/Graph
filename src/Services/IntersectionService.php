<?php declare(strict_types = 1);
namespace EugeneErg\Graph\Services;

use EugeneErg\Graph\Collections\BoolCollection;
use EugeneErg\Graph\Collections\BoolMatrix;
use EugeneErg\Graph\Collections\IntegerCollection;
use EugeneErg\Graph\Collections\IntegerMatrix;
use EugeneErg\Graph\Collections\IntersectionCollection;
use EugeneErg\Graph\ValueObjects\Canvas;
use EugeneErg\Graph\ValueObjects\ClearGraph;
use EugeneErg\Graph\ValueObjects\Intersection;

class IntersectionService extends AbstractService
{
    public function getIntersections(
        ClearGraph $branch,
        IntegerCollection $path,
        BoolCollection $outerVertexes
    ): IntersectionCollection {
        static $step = 0;
        $step++;

        $canvas = new Canvas($branch);
        CanvasService::instance()->pixels($canvas, $path, 1);
        $color = 1;
        $colors = new IntegerMatrix();
        $intersections = new BoolMatrix();

        foreach ($path as $vertexA) {
            foreach ($branch->getColumn($vertexA, true) ?? [] as $vertexB => $value) {
                $oldColor = $canvas[$vertexB];

                if ($oldColor !== 1) {
                    $intersections->setCell($oldColor === 0 ? $color + 1 : $oldColor, $vertexA, true);
                }

                if ($oldColor !== 0) {
                    continue;
                }

                $color++;
                $colors->setColumn($color, CanvasService::instance()->fill($canvas, $vertexB, $color));
            }
        }

        $outerColors = new BoolCollection();

        foreach ($outerVertexes as $outerVertex => $v) {
            $outerColors[$canvas[$outerVertex]] = true;
        }

        if ($step === 2) {
            //var_dump($intersections, $colors, $canvas);die;
        }

        return IntersectionCollection::fromWalk(
            $colors,
            function (IntegerCollection $vertexes, int $color) use ($intersections, $outerColors): Intersection {
                return new Intersection(
                    $vertexes,
                    $intersections->getChild($color),
                    isset($outerColors[$color]) ?: null
                );
            }
        );
    }

    public function isConflicted(
        Intersection $intersectionA,
        Intersection $intersectionB,
        IntegerCollection $path
    ): bool {
        $can = 0;
        $step = 0;

        foreach ($path as $vertex) {
            $aIsConnected = $intersectionA->connections[$vertex] ?? false;
            $bIsConnected = $intersectionB->connections[$vertex] ?? false;

            if (!$aIsConnected && !$bIsConnected) {
                continue;
            }

            if (!$can) {
                $can = 3 - (int) $aIsConnected - ($bIsConnected ? 2 : 0);
                $step += $can === 0;
            } elseif ($step === 1) {
                $step += ($can === 1 && $bIsConnected) || ($can === 2 && $aIsConnected);
            } else {
                $step += ($can === 1 && $aIsConnected) || ($can === 2 && $bIsConnected);
            }

            if ($step === 3) {
                return true;
            }
        }

        return false;
    }
}
