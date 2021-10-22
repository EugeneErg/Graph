<?php declare(strict_types = 1);
namespace EugeneErg\Graph\Services;

use EugeneErg\Graph\Collections\BoolCollection;
use EugeneErg\Graph\Collections\IntegerCollection;
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
        $canvas = new Canvas($branch);
        CanvasService::instance()->pixels($canvas, $path, 1);
        $color = 1;
        $colors = [];
        $intersections = [];

        foreach ($path as $vertexA) {
            foreach ($branch->getRow($vertexA) as $vertexB => $value) {
                $oldColor = $canvas->getColor($vertexB);

                if ($oldColor !== 1) {
                    $intersections[$oldColor === 0 ? $color + 1 : $oldColor][$vertexA] = true;
                }

                if ($oldColor !== 0) {
                    continue;
                }

                $colors[$color] = CanvasService::instance()->fill($canvas, $vertexB, ++$color);
            }
        }

        $outerColors = [];

        foreach ($outerVertexes as $outerVertex => $v) {
            $outerColors[$canvas->getColor($outerVertex)] = true;
        }

        $result = new IntersectionCollection();

        foreach ($colors as $color => $vertexes) {
            $result[] = new Intersection(
                $vertexes,
                $intersections[$color],
                isset($outerColors[$color]) ? true : null
            );
        }

        return $result;
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
