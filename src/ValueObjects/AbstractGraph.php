<?php declare(strict_types=1);

namespace EugeneErg\Graph\ValueObjects;

use EugeneErg\Graph\Collections\AbstractCollection2;
use EugeneErg\Graph\Collections\AbstractMatrix2;
use EugeneErg\Graph\Collections\IntegerCollection;
use JsonSerializable;

/**
 * @see AbstractGraph::getVertexes()
 * @property-read IntegerCollection $vertexes
 * @see AbstractGraph::getConnections()
 * @property-read AbstractMatrix2 $connections
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
                $connections->setItem(
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
        return $this->connections->getItem($column, $row, $nullIfNotExists);
    }

    public function setCell($column, $row, int $value)
    {
        return $this->connections->setItem($column, $row, $value);
    }

    public function issetCell($column, $row): bool
    {
        return $this->connections->issetItem($column, $row);
    }

    public function unsetCell($column, $row): void
    {
        $this->connections->unsetItem($column, $row);
    }

    public function getColumn($column, bool $nullIfNotExists = false): ?AbstractCollection2
    {
        return $this->connections->getCollection($column, $nullIfNotExists);
    }

    public function issetColumn($column): bool
    {
        return $this->connections->issetCollection($column);
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function getConnections(): AbstractMatrix2
    {
        return $this->connections;
    }
}
