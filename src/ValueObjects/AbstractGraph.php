<?php declare(strict_types=1);

namespace EugeneErg\Graph\ValueObjects;

use EugeneErg\Graph\Collections\AbstractCollection2;
use EugeneErg\Graph\Collections\AbstractMatrix2;
use EugeneErg\Graph\Collections\IntegerCollection;
use Generator;
use JsonSerializable;

/**
 * @see Graph::getVertexes()
 * @property-read IntegerCollection $vertexes
 */
abstract class AbstractGraph extends AbstractValueObject implements JsonSerializable
{
    private AbstractMatrix2 $connections;
    private IntegerCollection $vertexes;

    protected function setConnections(AbstractMatrix2 $connections): void
    {
        $this->connections = $connections;
    }

    protected function setVertexes(IntegerCollection $vertexes): void
    {
        $this->vertexes = $vertexes;
    }

    /** @return $this */
    public function createSupGraph(IntegerCollection $vertexes): self
    {
        /** @var AbstractMatrix2 $class */
        $class = get_class($this->connections);
        $connections = $class::fromArray();

        foreach ($vertexes->listBy($vertexes, 1) as [$vertexA, $vertexB]) {
            if ($this->issetCell($vertexA->value, $vertexB->value)) {
                $connections->setCell(
                    $vertexA->value,
                    $vertexB->value,
                    $this->getCell($vertexA->value, $vertexB->value)
                );
            }
        }

        return new static($connections, $vertexes->values());
    }

    public function direct(int $vertex): self
    {
        $parent = null;
        $new = clone $this;

        for ($vertexes = [$vertex => $parent]; $vertex !== null; $vertex = key($vertexes)) {
            if ($this->issetColumn($vertex)) {
                foreach ($this->getColumn($vertex) as $vertexB => $value) {
                    if ($vertexB !== $parent) {
                        $new->unsetCell($vertexB, $vertex);
                        $vertexes[$vertexB] = $vertex;
                    }
                }
            }

            $parent = next($vertexes);
        }

        return $new;
    }

    public function getVertexes(): AbstractCollection2
    {
        return $this->vertexes;
    }

    public function toArray(): array
    {
        return [
            'connections' => $this->connections->toArray(),
            'vertexes' => $this->vertexes,
        ];
    }

    public function getCell($column, $row, bool $nullIfNotExists = false)
    {
        return $this->connections->getCell($column, $row, $nullIfNotExists);
    }

    public function setCell($column, $row, int $value)
    {
        return $this->connections->setCell($column, $row, $value);
    }

    public function issetCell($column, $row): bool
    {
        return $this->connections->issetCell($column, $row);
    }

    public function unsetCell($column, $row): void
    {
        $this->connections->unsetCell($column, $row);
    }

    public function getColumn($column, bool $nullIfNotExists = false): ?Generator
    {
        return $this->connections->getColumn($column, $nullIfNotExists);
    }

    public function issetColumn($column): bool
    {
        return $this->connections->issetColumn($column);
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
