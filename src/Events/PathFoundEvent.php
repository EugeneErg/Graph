<?php declare(strict_types=1);
namespace EugeneErg\Graph\Events;

use EugeneErg\Graph\Collections\IntegerCollection;

class PathFoundEvent
{
    private array $steps;
    private IntegerCollection $vertexes;

    public function __construct(array $steps, IntegerCollection $vertexes)
    {
        $this->steps = $steps;
        $this->vertexes = $vertexes;
    }

    public function getVertexes(): IntegerCollection
    {
        return $this->vertexes;
    }

    public function getSteps(): array
    {
        return $this->steps;
    }
}
