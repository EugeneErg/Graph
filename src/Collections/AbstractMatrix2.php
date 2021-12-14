<?php declare(strict_types=1);

namespace EugeneErg\Graph\Collections;

use Generator;

class AbstractMatrix2 extends AbstractCollection2
{
    public function getCell($column, $row, bool $nullIfNotExists = false)
    {
        return $nullIfNotExists === false || $this->isset($column, $row) ? $this->get($column, $row) : null;
    }

    public function setCell($column, $row, $value)
    {
        return $this->set($column, $row, $value);
    }

    public function issetCell($column, $row): bool
    {
        return $this->isset($column, $row);
    }

    public function unsetCell($column, $row): void
    {
        $this->unset($column, $row);
    }

    public function getColumn($column, bool $nullIfNotExists = false): ?Generator
    {
        return $nullIfNotExists === false || $this->isset($column) ? $this->get($column) : null;
    }

    public function issetColumn($column): bool
    {
        return $this->isset($column);
    }

    public function setColumn($column, AbstractCollection2 $value): AbstractCollection2
    {
        return $this->set($column, $value);
    }
}
