<?php declare(strict_types = 1);
namespace EugeneErg\Graphs;

class AdjacencyMatrix extends Matrix
{
    /**
     * @var Canvas
     */
    private $canvas;

    /** @inheritDoc */
    public function __construct(array $matrix)
    {
        parent::__construct($matrix);
        $this->canvas = new Canvas($this);
    }

    public function hasConnection(int $vertexA, int $vertexB): bool
    {
        return $this->hasItem($vertexA, $vertexB) || $this->hasItem($vertexB, $vertexA);
    }

    public function getConnectionLevel(int $vertexA, int $vertexB): int
    {
        return (int) $this->hasItem($vertexA, $vertexB) + (int) $this->hasItem($vertexB, $vertexA);
    }

    public function findShortPath(int $vertexA, int $vertexB): ?array
    {
        $steps = [[$vertexB => null]];
        $values = [];
        $canvas = $this->canvas;

        for ($step = 0; $step < count($steps); $step++) {
            foreach ($steps[$step] as $currentVertex => $prevVertex) {
                $currentValue = !empty($values[$currentVertex]);
                unset($values[$currentVertex]);

                if ($this->hasItem($currentVertex, $vertexA)) {
                    $canvas = $canvas->pixel([$vertexA], 1);
                    $steps[$step + 1][$vertexA] = $currentVertex;

                    break(2);
                }

                foreach ($this->getRow($currentVertex) as $nextVertex => $value) {
                    if (
                        $canvas->getColor($nextVertex) === 0
                        && (
                            (!$currentValue && $value !== 3)
                            || ($currentValue && $value === 2)
                        )
                    ) {
                        $canvas = $canvas->pixel([$nextVertex], 1);
                        $steps[$step + 1][$nextVertex] = $currentVertex;

                        if ($currentValue) {
                            $values[$currentVertex] = true;
                        }
                    }
                }
            }
        }

        if ($canvas->getColor($vertexA) === 0) {
            return null;
        }

        $currentVertex = $vertexA;
        $result[] = $currentVertex;

        for ($step = count($steps) - 1; $step > 0; $step--) {
            $currentVertex = $steps[$step][$currentVertex];
            $result[] = $currentVertex;
        }

        return $result;
    }

    /**
     * @param int $vertexA
     * @param int $vertexB
     * @param bool $first
     * @return array|null
     */
    public function findShortEdge(int $vertexA, int $vertexB, bool $first = true): ?array
    {
        foreach ($this->getRow($vertexA) as $vertex => $value) {
            if (($value === 1 && !$first) || $vertex === $vertexB) {
                $this->unsetValue($vertex, $vertexA);
            }
        }

        $result = $this->findShortPath($vertexA, $vertexB);

        foreach ($this->getRow($vertexA) as $vertex => $value) {
            $this->setValue($vertex, $vertexA, $value);
        }

        return $result;
    }

    public function createSubMatrix(int ...$vertexes): self
    {
        $matrix = [];

        foreach ($vertexes as $newIndexA => $oldIndexA) {
            foreach ($vertexes as $newIndexB => $oldIndexB) {
                if ($this->hasItem($oldIndexA, $oldIndexB)) {
                    $matrix[$newIndexA][$newIndexB] = $this->getValue($oldIndexA, $oldIndexB);
                }
            }
        }

        return new self($matrix);
    }

    /**
     * @param int[] $edge
     * @param int $value
     */
    public function setEdgeValue(array $edge, int $value): void
    {
        $prev = null;

        foreach ($edge as $vertex) {
            if ($prev !== null) {
                $this->setValue($vertex, $prev, $value);
                $this->setValue($prev, $vertex, $value);
            }

            $prev = $vertex;
        }

        $first = reset($edge);
        $this->setValue($first, $prev, $value);
        $this->setValue($prev, $first, $value);
    }
    /**
     * @param int[] $edge
     */
    public function unsetEdgeValue(array $edge): void
    {
        $prev = null;

        foreach ($edge as $vertex) {
            if ($prev !== null) {
                $this->unsetValue($vertex, $prev);
                $this->unsetValue($prev, $vertex);
            }

            $prev = $vertex;
        }

        $first = reset($edge);
        $this->unsetValue($first, $prev);
        $this->unsetValue($prev, $first);
    }

    /**
     * @param int[] $edge
     * @param int $vertex
     * @param int $value
     */
    public function addEdgeValue(array $edge, int $vertex, int $value): void
    {
        foreach ($edge as $vertexB) {
            $this->setValue($vertex, $vertexB, $value);
            $this->setValue($vertexB, $vertex, $value);
        }
    }

    /**
     * @param array $vertexes
     */
    public function deleteValues(array $vertexes): void
    {
        foreach ($vertexes as $vertexA) {
            foreach ($this->getRow($vertexA) as $vertexB => $value) {
                $this->unsetValue($vertexA, $vertexB);
                $this->unsetValue($vertexB, $vertexA);
            }
        }
    }

    public function hasConnectionValue(int $vertex, int $connectionValue): bool
    {
        return \in_array($connectionValue, $this->getRow($vertex), true);
    }
}
