<?php declare(strict_types = 1);
namespace EugeneErg\Graphs;

class Matrix implements MatrixInterface
{
    /** @var int[][] */
    private $matrix;

    /**
     * Matrix2D constructor.
     * @param int[][] $matrix
     */
    public function __construct(array $matrix)
    {
        $this->matrix = $matrix;
    }

    public function hasItem(int $rowNumber, int $columnNumber): bool
    {
        return isset($this->matrix[$rowNumber][$columnNumber]);
    }

    public function getValue(int $rowNumber, int $columnNumber): ?int
    {
        return $this->matrix[$rowNumber][$columnNumber] ?? null;
    }

    public function setValue(int $rowNumber, int $columnNumber, int $value): void
    {
        $this->matrix[$rowNumber][$columnNumber] = $value;
    }

    public function unsetValue(int $rowNumber, int $columnNumber): void
    {
        unset($this->matrix[$rowNumber][$columnNumber]);
    }

    public function getRow(int $rowNumber): array
    {
        return $this->matrix[$rowNumber] ?? [];
    }

    public function toArray(): array
    {
        return $this->matrix;
    }

    public function getRowCount(): int
    {
        return count($this->matrix);
    }
}
