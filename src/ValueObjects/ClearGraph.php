<?php declare(strict_types = 1);
namespace EugeneErg\Graph\ValueObjects;

use EugeneErg\Graph\Collections\BoolMatrix;
use EugeneErg\Graph\Collections\Collection;
use EugeneErg\Graph\Collections\IntegerCollection;
use EugeneErg\Graph\Services\Assert\Argument;
use EugeneErg\Graph\Services\AssertService;

class ClearGraph extends AbstractGraph
{
    public function __construct(BoolMatrix $connections, ?IntegerCollection $vertexes = null)
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
                [Graph::class, '__construct']
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

        $result = new self(new BoolMatrix(), $graph->vertexes);

        foreach ($graph->vertexes as $vertexA) {
            foreach ($graph->vertexes as $vertexB) {
                if ($graph->issetCell($vertexA, $vertexB) && !empty($graph->getCell($vertexA, $vertexB))) {
                    $result->setCell($vertexA, $vertexB, true);
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
            $this->getCell($currentVertex, $prevVertex) === 2
                ? $this->unsetCell($currentVertex, $prevVertex)
                : $this->setCell($currentVertex, $prevVertex, 1);
            $prevVertex = $currentVertex;
        }
    }

    public function deleteConnections(IntegerCollection $vertexes): void
    {
        foreach ($vertexes as $vertexA) {
            if ($this->issetColumn($vertexA)) {
                foreach ($this->getColumn($vertexA) as $vertexB => $value) {
                    $this->unsetCell($vertexA, $vertexB);
                }
            }
        }
    }

    public function setCell($column, $row, $value): int
    {
        parent::setCell($column, $row, $value);

        return parent::setCell($row, $column, $value);
    }

    public function unsetCell($column, $row): void
    {
        parent::unsetCell($column, $row);
        parent::unsetCell($row, $column);
    }
}