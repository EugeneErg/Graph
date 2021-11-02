<?php declare(strict_types = 1);
namespace EugeneErg\Graph\ValueObjects;

use EugeneErg\Graph\Collections\Collection;
use EugeneErg\Graph\Collections\IntegerCollection;
use EugeneErg\Graph\Collections\IntegerMatrix;
use EugeneErg\Graph\Services\Assert\Argument;
use EugeneErg\Graph\Services\AssertService;

/**
 * @see Graph::getVertexes()
 * @property-read IntegerCollection $vertexes
 * @see Graph::getConnections()
 * @property-read IntegerMatrix $connections
 */
class Graph extends AbstractValueObject
{
    /** @var IntegerMatrix */
    private $connections;
    /** @var IntegerCollection  */
    private $vertexes;

    public function __construct(IntegerMatrix $connections, ?IntegerCollection $vertexes)
    {
        $this->connections = $connections;
        $realVertex = IntegerCollection::fromKeys(
            Collection::fromReplace(false, $this->connections, ...$this->connections)
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

        $this->vertexes = $vertexes ?? $realVertex;
    }

    public function createSupGraph(IntegerCollection $vertexes): Graph
    {
        $connections = new IntegerMatrix();
        $vertexes->foreach(
            function (int $vertexA) use ($vertexes, $connections): void {
                $vertexes->foreach(function (int $vertexB) use ($vertexA, $connections): void {
                    if (isset($this->connections[$vertexA][$vertexB])) {
                        $connections->set($this->connections[$vertexA][$vertexB], $vertexA, $vertexB);
                    }
                });
            }
        );

        return new static($connections, $vertexes->values());
    }

    public function direct(int $vertex): Graph
    {
        $parent = null;
        $connections = $this->connections;

        for ($vertexes = [$vertex => $parent]; $vertex !== null; $vertex = key($vertexes)) {
            foreach ($this->connections[$vertex] ?? [] as $vertexB => $value) {
                if ($vertexB !== $parent) {
                    unset($connections[$vertexB][$vertex]);
                    $vertexes[$vertexB] = $vertex;
                }
            }

            $parent = next($vertexes);
        }

        return new Graph($connections, $this->vertexes);
    }

    public function getConnections(): IntegerMatrix
    {
        return $this->connections;
    }

    public function getVertexes(): IntegerCollection
    {
        return $this->vertexes;
    }
}
