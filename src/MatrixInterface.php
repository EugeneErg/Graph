<?php namespace EugeneErg\Graphs;

interface MatrixInterface
{
    public function hasItem(int $rowNumber, int $columnNumber): bool;
    public function getValue(int $rowNumber, int $columnNumber): ?int;
    public function getRow(int $rowNumber): array;
    public function getRowCount(): int;
    public function toArray(): array;
}