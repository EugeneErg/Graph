<?php declare(strict_types = 1);
namespace EugeneErg\Graph\ValueObjects;

use EugeneErg\Graph\Collections\Collection;
use EugeneErg\Graph\Collections\IntegerCollection;
use EugeneErg\Graph\Collections\IntegerMatrix;
use EugeneErg\Graph\Services\Assert\Argument;
use EugeneErg\Graph\Services\AssertService;

/**
 * @see ClearGraph::setOuterEdgeAttribute()
 * @method void setOuterEdge(array $path)
 * @see ClearGraph::joinOuterEdgeAttribute()
 * @method void joinOuterEdge(array $path)
 * @see ClearGraph::deleteConnectionsAttribute()
 * @method void deleteConnections(array $vertexes)
 */
class ClearGraph extends Graph
{
    public function __construct(IntegerMatrix $connections, ?array $vertexes = null)
    {
        $connections->foreach(function (int $value, int $vertexA, int $vertexB) use ($connections): void {
            AssertService::instance()->equal(
                new Argument($value, 1, "connections[{$vertexA}][{$vertexB}]"),
                new Argument($connections[$vertexB][$vertexA] ?? null, 1, "connections[{$vertexB}][{$vertexA}]"),
                null,
                [ClearGraph::class, '__construct']
            );
        }, 2);

        parent::__construct($connections, $vertexes);
    }

    public function setOuterEdgeAttribute(object $attributes, array $path): void
    {
        $prev = end($path);

        foreach ($path as $vertex) {
            $attributes->connections[$prev][$vertex] = $attributes->connections[$vertex][$prev] = 2;
            $prev = $vertex;
        }
    }

    public function joinOuterEdgeAttribute(object $attributes, array $path): void
    {
        $prevVertex = end($path);

        foreach ($path as $currentVertex) {
            $value = $attributes->connections[$currentVertex][$prevVertex];

            if ($value === 2) {
                unset(
                    $attributes->connections[$currentVertex][$prevVertex],
                    $attributes->connections[$prevVertex][$currentVertex]
                );
            } else {
                $attributes->connections[$currentVertex][$prevVertex]
                    = $attributes->connections[$prevVertex][$currentVertex] = $value + 1;
            }

            $prevVertex = $currentVertex;
        }
    }

    public function deleteConnectionsAttribute(object $attributes, array $vertexes): void
    {
        foreach ($vertexes as $vertexA) {
            foreach ($attributes->connections[$vertexA] ?? [] as $vertexB => $value) {
                unset($attributes->connections[$vertexA][$vertexB], $attributes->connections[$vertexB][$vertexA]);
            }
        }
    }
}