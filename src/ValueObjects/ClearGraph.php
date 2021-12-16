<?php declare(strict_types = 1);
namespace EugeneErg\Graph\ValueObjects;

use EugeneErg\Graph\Collections\Collection;
use EugeneErg\Graph\Collections\IntegerCollection;
use EugeneErg\Graph\Collections\IntegerMatrix;
use EugeneErg\Graph\Services\Assert\Argument;
use EugeneErg\Graph\Services\AssertService;

class ClearGraph extends AbstractGraph
{
    public function __construct(IntegerMatrix $connections, ?IntegerCollection $vertexes = null)
    {
        $realVertex = IntegerCollection::fromKeys(
            Collection::fromReplace(false, $connections, ...$connections)
        );

        if ($vertexes !== null) {
            $difference = $realVertex->difference($vertexes);
            AssertService::instance()->equals(
                true,
                new Argument($difference->isEmpty(), 2, 'vertexes'),
                'Does not contain vertices ' . $difference->implode(',')
                . ' present in ' . new Argument($connections, 1, 'connections'),
                [ClearGraph::class, '__construct']
            );
        }

        foreach ($connections->listBy(2) as [$vertexA, $vertexB]) {
            AssertService::instance()->equals(
                new Argument($vertexB->value, 1, "connections[{$vertexA->key}][{$vertexB->key}]"),
                new Argument(
                    $connections->getCell($vertexB->key, $vertexA->key, true),
                    1,
                    "connections[{$vertexB->key}][{$vertexA->key}]"
                ),
                null,
                [ClearGraph::class, '__construct']
            );
        }

        $this->setConnections($connections);
        $this->setVertexes($vertexes ?? $realVertex);
    }

    public static function fromGraph(AbstractGraph $graph): self
    {
        if ($graph instanceof self) {
            return $graph;
        }

        $result = new self(new IntegerMatrix(), $graph->vertexes);

        foreach ($graph->vertexes as $vertexA) {
            foreach ($graph->vertexes as $vertexB) {
                if ($graph->issetCell($vertexA, $vertexB) && !empty($graph->getCell($vertexA, $vertexB))) {
                    $result->setCell($vertexA, $vertexB, 1);
                }
            }
        }

        return $result;
    }

    public function setOuterEdge(IntegerCollection $path): void
    {
        $prev = $path->getValueByPosition(-1);

        foreach ($path as $vertex) {
            $this->setCell($prev, $vertex, 2);
            $prev = $vertex;
        }
    }

    public function joinOuterEdge(IntegerCollection $path): void
    {
        $prevVertex = $path->getValueByPosition(-1);

        foreach ($path as $currentVertex) {
            $value = $this->getCell($currentVertex, $prevVertex);
            $value === 2
                ? $this->unsetCell($currentVertex, $prevVertex)
                : $this->setCell($currentVertex, $prevVertex, $value + 1);

            $prevVertex = $currentVertex;
        }
    }

    public function deleteConnections(IntegerCollection $vertexes): void
    {
        foreach ($vertexes as $vertexA) {
            foreach ($this->getColumn($vertexA, true) ?? [] as $vertexB => $value) {
                $this->unsetCell($vertexA, $vertexB);
            }
        }
    }

    public function setCell($column, $row, $value, bool $direction = false): int
    {
        $result = parent::setCell($column, $row, $value);

        if (!$direction) {
            parent::setCell($row, $column, $value);
        }

        return $result;
    }

    public function unsetCell($column, $row, bool $direction = false): void
    {
        parent::unsetCell($column, $row);

        if (!$direction) {
            parent::unsetCell($row, $column);
        }
    }
}