<?php declare(strict_types = 1);
namespace EugeneErg\Graph\ValueObjects;

use EugeneErg\Graph\Collections\IntegerCollection;
use EugeneErg\Graph\Collections\IntegerMatrix;
use EugeneErg\Graph\Services\Assert\Argument;
use EugeneErg\Graph\Services\AssertService;

class ClearGraph extends Graph
{
    public function __construct(IntegerMatrix $connections, ?IntegerCollection $vertexes = null)
    {
        foreach ($connections->listBy(2) as $data) {
            [$vertexA, $vertexB] = $data->allKeys();
            AssertService::instance()->equals(
                new Argument($data[1]->value, 1, "connections[{$vertexA}][{$vertexB}]"),
                new Argument(
                    $connections[$vertexB][$vertexA] ?? null,
                    1,
                    "connections[{$vertexB}][{$vertexA}]"
                ),
                null,
                [ClearGraph::class, '__construct']
            );
        }

        parent::__construct($connections, $vertexes);
    }

    public function setOuterEdge(IntegerCollection $path): void
    {
        $prev = $path->end();

        foreach ($path as $vertex) {
            $this->connections[$prev][$vertex] = $this->connections[$vertex][$prev] = 2;
            $prev = $vertex;
        }
    }

    public function joinOuterEdge(IntegerCollection $path): void
    {
        $prevVertex = $path->end();

        foreach ($path as $currentVertex) {
            $value = $this->connections[$currentVertex][$prevVertex];

            if ($value === 2) {
                unset(
                    $this->connections[$currentVertex][$prevVertex],
                    $this->connections[$prevVertex][$currentVertex]
                );
            } else {
                $this->connections[$currentVertex][$prevVertex]
                    = $this->connections[$prevVertex][$currentVertex] = $value + 1;
            }

            $prevVertex = $currentVertex;
        }
    }

    public function deleteConnections(IntegerCollection $vertexes): void
    {
        foreach ($vertexes as $vertexA) {
            foreach ($this->connections[$vertexA] ?? [] as $vertexB => $value) {
                unset($this->connections[$vertexA][$vertexB], $this->connections[$vertexB][$vertexA]);
            }
        }
    }
}