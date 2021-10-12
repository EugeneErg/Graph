<?php namespace EugeneErg\Graphs\ValueObjects;

use EugeneErg\Graphs\ValueObjects\Collections\AbstractCollection;
use EugeneErg\Graphs\ValueObjects\Collections\CollectionCollection;

/**
 * @property-read Vector[] $records
 * @method Vector[] getRecords()
 * @see Matrix::getRotate()
 * @property-read Matrix $rotate
 */
class Matrix extends CollectionCollection
{
    public function __construct(Vector ...$vectors)
    {
        foreach ($vectors as $vector) {
            if ($vector->count !== $vectors[0]->count) {
                throw new \Exception('Dimensions do not match');
            }
        }

        parent::__construct($vectors);
    }

    public static function createByVectorProduct(Vector $vectorA, Vector $vectorB): self
    {
        return static::map(static function (float $valueA) use ($vectorB): Vector {
            return Vector::map(static function (float $valueB) use ($valueA): float {
                return $valueA * $valueB;
            }, $vectorB);
        }, $vectorA);
    }

    public static function createMirror(callable $callback, int $size): self
    {
        $result = [];

        for ($row = 0; $row < $size; $row++) {
            for ($col = $row; $col < $size; $col++) {
                $result[$col][$row] = $result[$row][$col] = $callback($row, $col);
            }
        }

        return Matrix::map(static function (array $vector): Vector {
            return new Vector(...$vector);
        }, new class ($result) extends AbstractCollection {});
    }

    public function getColCount(): int
    {
        return $this[0]->count ?? 0;
    }

    public function getRowCount(): int
    {
        return $this->count;
    }

    /**
     * @param int $offset
     * @param Vector $value
     * @throws \Exception
     */
    public function offsetSet($offset, $value): void
    {
        if (!isset($this[$offset]) || !$value instanceof Vector) {
            throw new \Exception('impossible set value');
        }

        $this->records[$offset] = $value;
    }

    /**
     * @param Matrix $matrix
     * @return $this
     */
    public function minus(Matrix $matrix): self
    {
        return static::map(static function (Vector $vectorA, Vector $vectorB): Vector {
            return $vectorA->minus($vectorB);
        }, $this, $matrix);
    }

    public function product(float $value): Matrix
    {
        return static::map(static function (Vector $vector) use ($value): Vector {
            return $vector->product($value);
        }, $this);
    }

    private function productMatrix(Matrix $matrix): Matrix
    {
        return static::map(static function (Vector $vectorA) use ($matrix): Vector {
            return Vector::map(static function (float ...$values) use ($vectorA): float {
                return $vectorA->productVector(new Vector(...$values));
            }, ...$matrix);
        }, $this);
    }

    public function getColVector(int $col): Vector
    {
        return new Vector(...array_column($this->records, $col));
    }

    public static function create(array $records = []): Collections\AbstractCollection
    {
        return new static(...$records);
    }

    public function getRotate(): Matrix
    {
        return static::map(static function (float ...$values): Vector {
            return new Vector(...$values);
        }, ...$this);
    }
}
