<?php declare(strict_types = 1);
namespace EugeneErg\Graph\ValueObjects;

class Arc
{
    /** @var int[][] */
    private $vertexes;
    /** @var GravityInterface */
    private $gravity;

    /**
     * @param int[] ...$vertexes
     * @param GravityInterface $gravity
     */
    public function __construct(GravityInterface $gravity, array ...$vertexes)
    {
        $this->vertexes = $vertexes;
        $this->gravity = $gravity;
    }

    public function getGravity(): GravityInterface
    {
        return $this->gravity;
    }

    /** @return int[][] */
    public function getVertexes(): array
    {
        return $this->vertexes;
    }

    public function firstVertex(): int
    {
        return $this->vertexes[0][0];
    }

    public function lastVertex(): int
    {
        $end = end($this->vertexes);

        return end($end);
    }

    /** @return float[] */
    public function getIndexes(): array
    {
        $result = [];
        $count = count($this->vertexes);

        foreach ($this->vertexes as $num1 => $vertexes) {
            $step = 1 / (count($vertexes) * $count - 1);
            $prevCount = $num1 * count($vertexes);

            foreach ($vertexes as $num2 => $vertex) {
                $result[$vertex] = $step * ($num2 + $prevCount);
            }
        }

        return $result;
    }
}
