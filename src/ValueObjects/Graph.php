<?php declare(strict_types = 1);
namespace EugeneErg\Graph\ValueObjects;

use EugeneErg\Graph\Collections\Collection;
use EugeneErg\Graph\Collections\IntegerCollection;
use EugeneErg\Graph\Collections\IntegerMatrix;

/**
 * @property-read int[] $vertexes
 * @property-read int[][] $connections
 * @see Graph::setValueAttribute()
 * @method void setValue(int $vertexA, int $vertexB, int $value)
 * @see Graph::unsetValueAttribute()
 * @method int|null unsetValue(int $vertexA, int $vertexB)
 * @see Graph::addVertexAttribute()
 * @method int addVertex(int $vertex)
 */
class Graph extends AbstractValueObjectMutable
{
    /**
     * @param int[][] $connections
     * @param int[] $vertexes
     */
    public function __construct(IntegerMatrix $connections, ?array $vertexes = null)
    {
        $clearConnections = Collection::map(function (IntegerCollection $integerCollection): array {
            return $integerCollection->toArray();
        }, $connections)->toArray();

        parent::__construct(
            $clearConnections,
            $vertexes ?? array_keys(array_replace($clearConnections, ...$clearConnections))
        );
    }

    public function hasConnection(int $vertexA, int $vertexB, bool $considerDirected = true): bool
    {
        return isset($this->connections[$vertexA][$vertexB])
            || (!$considerDirected && isset($this->connections[$vertexB][$vertexA]));
    }

    public function getRow(int $vertex): array
    {
        return $this->connections[$vertex] ?? [];
    }

    public function getValue(int $vertexA, int $vertexB): ?int
    {
        return $this->connections[$vertexA][$vertexB] ?? null;
    }

    public function addVertexAttribute(object $attributes, int $vertex): int
    {
        $result = count($attributes->vertexes);
        $attributes->vertexes[] = $vertex;

        return $result;
    }

    public function createSupGraph(int ...$vertexes): Graph
    {
        $connections = new IntegerMatrix();

        foreach ($vertexes as $vertexA) {
            foreach ($vertexes as $vertexB) {
                $value = $this->getValue($vertexA, $vertexB);

                if ($value !== null) {
                    $connections[$vertexA][$vertexB] = $value;
                }
            }
        }

        $class = get_class($this);

        return new $class($connections, $vertexes);
    }

    public function unsetValueAttribute(object $attributes, int $vertexA, int $vertexB): ?int
    {
        $result = $attributes->connections[$vertexA][$vertexB] ?? null;
        unset($attributes->connections[$vertexA][$vertexB]);

        return $result;
    }

    public function setValueAttribute(object $attributes, int $vertexA, int $vertexB, int $value): void
    {
        $attributes->connections[$vertexA][$vertexB] = $value;
    }

    public function direct(int $vertex): Graph
    {
        $parent = null;
        $connections = $this->connections;

        for ($vertexes = [$vertex => $parent]; $vertex !== null; $vertex = key($vertexes)) {
            foreach ($this->getRow($vertex) as $vertexB => $value) {
                if ($vertexB !== $parent) {
                    unset($connections[$vertexB][$vertex]);
                    $vertexes[$vertexB] = $vertex;
                }
            }

            $parent = next($vertexes);
        }

        return new Graph($connections, $this->vertexes);
    }

    public function setRow(int $vertex, array $raw): void
    {
        $this->getAttributes()->connections[$vertex] = $raw;
    }
}
