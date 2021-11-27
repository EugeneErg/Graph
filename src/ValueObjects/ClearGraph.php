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
        foreach ($connections->listBy(2) as [$vertexA, $vertexB]) {
            AssertService::instance()->equals(
                new Argument($vertexB->value, 1, "connections[{$vertexA->key}][{$vertexB->key}]"),
                new Argument(
                    $connections[$vertexB->key][$vertexA->key] ?? null,
                    1,
                    "connections[{$vertexB->key}][{$vertexA->key}]"
                ),
                null,
                [ClearGraph::class, '__construct']
            );
        }

        parent::__construct($connections, $vertexes);
    }

    public function setOuterEdge(IntegerCollection $path): void
    {
        $prev = $path->getValueByPosition(-1);

        foreach ($path as $vertex) {
            $this[$prev][$vertex] = 2;
            $this[$vertex][$prev] = 2;
            $prev = $vertex;
        }
    }

    public function joinOuterEdge(IntegerCollection $path): void
    {
        $prevVertex = $path->getValueByPosition(-1);

        foreach ($path as $currentVertex) {
            $value = $this[$currentVertex][$prevVertex];

            if ($value === 2) {
                unset(
                    $this[$currentVertex][$prevVertex],
                    $this[$prevVertex][$currentVertex]
                );
            } else {
                $this[$currentVertex][$prevVertex] = $value + 1;
                $this[$prevVertex][$currentVertex] = $value + 1;
            }

            $prevVertex = $currentVertex;
        }
    }

    public function deleteConnections(IntegerCollection $vertexes): void
    {
        foreach ($vertexes as $vertexA) {
            foreach ($this[$vertexA] ?? [] as $vertexB => $value) {
                unset($this[$vertexA][$vertexB], $this[$vertexB][$vertexA]);
            }
        }
    }
}