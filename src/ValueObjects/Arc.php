<?php declare(strict_types = 1);
namespace EugeneErg\Graph\ValueObjects;

use EugeneErg\Graph\Collections\IntegerCollection;
use EugeneErg\Graph\Collections\IntegerMatrix;

class Arc extends AbstractValueObject
{
    private IntegerMatrix $vertexes;
    private GravityInterface $gravity;

    public function __construct(GravityInterface $gravity, IntegerMatrix $vertexes)
    {
        $this->vertexes = $vertexes;
        $this->gravity = $gravity;
    }

    public function getGravity(): GravityInterface
    {
        return $this->gravity;
    }

    public function getVertexes(): IntegerMatrix
    {
        return $this->vertexes;
    }

    public function firstVertex(): int
    {
        return $this->vertexes->getItem(0, 0);
    }

    public function lastVertex(): int
    {
        return $this->vertexes->getValueByPosition()->getValueByPosition();
    }

    /** @return float[] */
    public function getIndexes(): array
    {
        $result = [];
        $count = $this->vertexes->count();

        foreach ($this->vertexes as $num1 => $vertexes) {
            $step = 1 / ($vertexes->count() * $count - 1);
            $prevCount = $num1 * $vertexes->count();

            foreach ($vertexes as $num2 => $vertex) {
                $result[$vertex] = $step * ($num2 + $prevCount);
            }
        }

        return $result;
    }

    public function toArray(): array
    {
        return [
            'vertexes' => $this->vertexes->toArray(),
            'gravity' => $this->gravity->toArray(),
        ];
    }
}
