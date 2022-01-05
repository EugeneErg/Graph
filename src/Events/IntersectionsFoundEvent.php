<?php declare(strict_types=1);
namespace EugeneErg\Graph\Events;

use EugeneErg\Graph\Collections\IntersectionCollection;
use EugeneErg\Graph\ValueObjects\ClearGraph;

class IntersectionsFoundEvent
{
    private IntersectionCollection $intersections;
    private ClearGraph $intersectionMatrix;

    public function __construct(IntersectionCollection $intersections, ClearGraph $intersectionMatrix)
    {
        $this->intersections = $intersections;
        $this->intersectionMatrix = $intersectionMatrix;
    }

    public function getIntersections(): IntersectionCollection
    {
        return $this->intersections;
    }

    public function getIntersectionMatrix(): ClearGraph
    {
        return $this->intersectionMatrix;
    }
}
