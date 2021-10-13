<?php declare(strict_types = 1);
namespace EugeneErg\Graph\ValueObjects;

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
    public function __construct(array $connections, ?array $vertexes = null)
    {
        $clearConnections = [];

        foreach ($connections as $vertexA => $connection) {
            foreach ($connection as $vertexB => $value) {
                if ($value !== null) {
                    $clearConnections[$vertexA][$vertexB] = $clearConnections[$vertexB][$vertexA] = 1;
                }
            }
        }

        parent::__construct($clearConnections, $vertexes);
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