<?php namespace EugeneErg\Graphs;

class Canvas
{
    /** @var int[] */
    private $vertexes;
    /** @var AdjacencyMatrix */
    public $matrix;
    /** @var int[]|null */
    private $operations;
    /** @var int[] */
    private $colorCounts;
    /** @var int */
    private $maxColor;

    public function __construct(AdjacencyMatrix $matrix)
    {
        $this->vertexes = [];
        $this->matrix = $matrix;
        $this->operations = null;
        $this->colorCounts = [$matrix->getRowCount()];
        $this->maxColor = 0;
    }

    /**
     * @return int[]
     */
    public function getVertexes(): array
    {
        return $this->vertexes;
    }

    public function getColorCount(int $color): int
    {
        return $this->colorCounts[$color] ?? 0;
    }

    public function getMaxColor(): int
    {
        return $this->maxColor;
    }

    /**
     * @return int[]|null
     */
    public function getOperations(): ?array
    {
        return $this->operations;
    }

    public function getColor(int $vertexId): int
    {
        return $this->vertexes[$vertexId] ?? 0;
    }

    /**
     * @param int[] $vertexes
     * @param int|null $color
     * @return $this
     */
    public function pixel(array $vertexes, ?int $color = null): self
    {
        $result = $this->getChildren();
        $color = $color ?? $this->maxColor + 1;
        $result->maxColor = max($color, $this->maxColor);

        foreach ($vertexes as $vertexId) {
            $result->colorCounts[$result->vertexes[$vertexId] ?? 0]--;
            $result->vertexes[$vertexId] = $color;
            $result->operations[$vertexId] = $vertexId;
        }

        if (!isset($result->colorCounts[$color])) {
            $result->colorCounts[$color] = count($vertexes);
        } else {
            $result->colorCounts[$color] += count($vertexes);
        }

        return $result;
    }

    public function fill(int $vertexId, ?int $color = null): self
    {
        $result = $this->getChildren();
        $oldColor = $result->vertexes[$vertexId] ?? 0;
        $newColor = $color ?? $this->maxColor + 1;
        $result->maxColor = max($newColor, $this->maxColor);
        $result->vertexes[$vertexId] = $newColor;
        $result->operations[$vertexId] = $vertexId;
        reset($result->operations);

        while ($vertexId !== false) {
            foreach ($this->matrix->getRow($vertexId) as $connectionVertexId => $value) {
                if (($result->vertexes[$connectionVertexId] ?? 0) === $oldColor) {
                    $result->vertexes[$connectionVertexId] = $newColor;
                    $result->operations[$connectionVertexId] = $connectionVertexId;
                }
            }

            $vertexId = next($result->operations);
        }

        $result->colorCounts[$newColor] = ($result->colorCounts[$newColor] ?? 0) + count($result->operations);
        $result->colorCounts[$oldColor] -= count($result->operations);

        return $result;
    }

    public function incFill(int $vertexId, int $color = null): self
    {
        $result = $this->getChildren();
        $oldColor = $result->vertexes[$vertexId] ?? 0;
        $newColor = $color ?? $this->maxColor + 1;
        $result->maxColor = max($newColor, $this->maxColor);
        $result->vertexes[$vertexId] = $newColor;
        $result->operations[$vertexId] = $vertexId;
        reset($result->operations);
        $connect = [];

        while ($vertexId !== false) {
            if (!isset($connect[$vertexId])) {
                foreach ($this->matrix->getRow($vertexId) as $connectionVertexId => $value) {
                    if (($result->vertexes[$connectionVertexId] ?? 0) === $oldColor) {
                        $result->vertexes[$connectionVertexId] = $newColor;
                        $result->operations[$connectionVertexId] = $connectionVertexId;
                    } elseif (
                        ($result->vertexes[$connectionVertexId] ?? 0) === $newColor
                        && !isset($result->operations[$connectionVertexId])
                    ) {
                        $result->operations[$connectionVertexId] = $connectionVertexId;
                        $connect[$connectionVertexId] = true;
                    }
                }
            }

            $vertexId = next($result->operations);
        }

        $result->colorCounts[$newColor] = ($result->colorCounts[$newColor] ?? 0) + count($result->operations)
            - count($connect);
        $result->colorCounts[$oldColor] -= count($result->operations) + count($connect);

        return $result;
    }

    private function getChildren(): self
    {
        $result = new self($this->matrix);
        $result->vertexes = $this->vertexes;
        $result->operations = [];
        $result->colorCounts = $this->colorCounts;

        return $result;
    }
}
