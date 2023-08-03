<?php

declare(strict_types=1);

namespace EugeneErg\Graph\New\ValueObjects;
use EugeneErg\Collections\IntegerCollection;
use EugeneErg\Collections\StringCollection;
use EugeneErg\Graph\New\Collections\IntegerMatrix;

class Graph
{
    public readonly IntegerMatrix $connections;
    public readonly IntegerCollection $vertexes;
    public readonly bool $directed;
    public readonly bool $weighted;

    public function __construct(IntegerMatrix $connections, ?bool $directed = null, ?bool $weighted = null)
    {
        $this->vertexes = IntegerCollection::fromKeys($connections);
        $this->connections = IntegerMatrix::fromColumn(
            $this->vertexes,
            fn() => new IntegerCollection(immutable: false),
            fn(int $vertex) => $vertex,
        );
        $realWeighted = false;
        $realDirected = false;

        foreach ($connections as $vertexA => $connection) {
            $newConnection = $this->connections[$vertexA];

            foreach ($connection as $vertexB => $value) {
                $newConnection->set($weighted === false ? 1 : $value, $vertexB);

                if ($value !== 1 && $weighted === null) {
                    $realWeighted = true;
                }

                if ($directed === false) {
                    $this->connections[$vertexB]->set($weighted === false ? 1 : $value, $vertexA);
                } elseif ($directed === null && !isset($connections[$vertexB][$vertexA])) {
                    $realDirected = true;
                }
            }
        }

        foreach ($this->connections as $connection) {
            $connection->setImmutable();
        }

        $this->directed = $realDirected || $directed;
        $this->weighted = $realWeighted || $weighted;
    }

    public function clone(?bool $directed = null, ?bool $weighted = null): self
    {
        $directed ??= $this->directed;
        $weighted ??= $this->weighted;

        return $directed === $this->directed && $weighted === $this->weighted
            ? $this
            : new self($this->connections, $directed, $weighted);
    }

    public static function createFromStrings(StringCollection $data, ?bool $directed = null, ?bool $weighted = null): self
    {
        return new static(IntegerMatrix::fromMap(
            function (string $row): IntegerCollection {
                $result = new IntegerCollection(immutable: false);

                foreach (str_split($row) as $vertex => $value) {
                    if (is_numeric($value)) {
                        $result[$vertex] = (int)$value;
                    }
                }

                return $result->setImmutable();
            },
            $data,
        ), $directed, $weighted);
    }

    public static function createFromString(string $data, ?bool $directed = null, ?bool $weighted = null): self
    {
        $rows = explode("\n", $data);
        $firstClearRows = 0;
        $hasNotClearRow = false;
        $minSpaces = null;

        foreach ($rows as $row) {
            if (!$hasNotClearRow) {
                $hasNotClearRow = !preg_match('{^\s+$}', $row);
                $firstClearRows += !$hasNotClearRow;
            }

            if (preg_match('{^\s+}', $row, $matches)) {
                [$spaces] = $matches;
                $minSpaces = $minSpaces === null ? strlen($spaces) : min($minSpaces, strlen($spaces));
            }
        }

        $missedCols = max(0, $minSpaces - $firstClearRows);
        $missedRows = $minSpaces - $missedCols;
        $result = new StringCollection(immutable: false);

        for ($i = $missedRows; $i < count($rows); $i++) {
            $result[] = substr($rows[$i], $missedCols);
        }

        return static::createFromStrings($result->setImmutable(), $directed, $weighted);
    }

    /*public function setConnection(int $vertexA, int $vertexB, int $value = 1, ?bool $directed = null): void
    {
        $directed = $directed ?? $this->directed;

        if ($directed && !$this->directed) {
            throw new \LogicException('Graph is not directed.');
        }

        if ($value !== 1 && !$this->weighted) {
            throw new \LogicException('Graph is not weighted.');
        }

        if (!$directed) {
            $this->connections[$vertexB][$vertexA] = $value;
        }

        $this->connections[$vertexA][$vertexB] = $value;
    }

    public function unsetConnection(int $vertexA, int $vertexB, ?bool $directed = null): void
    {
        $directed = $directed ?? $this->directed;

        if ($directed && !$this->directed) {
            throw new \LogicException('Graph is not directed.');
        }

        if (!$directed) {
            unset($this->connections[$vertexB][$vertexA]);
        }

        unset($this->connections[$vertexA][$vertexB]);
    }*/

    public function hasConnection(int $vertexA, int $vertexB, ?bool $directed = null): void
    {
        $directed = $directed ?? $this->directed;

        if (!$directed) {
            unset($this->connections[$vertexB][$vertexA]);
        }

        unset($this->connections[$vertexA][$vertexB]);
    }

    public function createSupGraph(IntegerCollection $vertexes): static
    {
        $connections = new IntegerMatrix(immutable: false);

        foreach ($vertexes as $vertexA) {
            $connection = new IntegerCollection(immutable: false);

            foreach ($vertexes as $vertexB) {
                if (isset($this->connections[$vertexA][$vertexB])) {
                    $connection[$vertexB] = $this->connections[$vertexA][$vertexB];
                }
            }

            $connections[$vertexA] = $connection->setImmutable();
        }

        return new static($connections);
    }

    public function direct(int $vertex): self
    {
        $parent = null;
        $connections = $this->connections->toArrayRecursive();

        for (
            $vertexes = [$vertex => $parent];
            $vertex !== null;
            $parent = next($vertexes),
            $vertex = key($vertexes)
        ) {
            foreach ($this->connections[$vertex] as $vertexB => $value) {
                if ($vertexB !== $parent) {
                    unset($connections[$vertexB][$vertex]);
                    $vertexes[$vertexB] = $vertex;
                }
            }
        }

        return new static(IntegerMatrix::fromMixedArray($connections, fn(array $item) => new IntegerCollection($item)));
    }

    public function toArray(): array
    {
        return [
            'connections' => $this->connections,
            'vertexes' => $this->vertexes,
            'weighted' => $this->weighted,
            'directed' => $this->directed,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function getColumn(int $column): ?IntegerCollection
    {
        return $this->connections[$column] ?? null;
    }
    /*
        public function getCell($column, $row, bool $nullIfNotExists = false)
        {
            return $this->connections->getItem($column, $row, $nullIfNotExists);
        }

        public function setCell($column, $row, int $value): void
        {
            $this->connections->setItem($column, $row, $value);
        }

        public function issetCell($column, $row): bool
        {
            return $this->connections->issetItem($column, $row);
        }

        public function unsetCell($column, $row): void
        {
            $this->connections->unsetItem($column, $row);
        }

        public function issetColumn($column): bool
        {
            return $this->connections->issetCollection($column);
        }

        public function getConnections(): IntegerMatrix
        {
            return $this->connections;
        }*/
}
