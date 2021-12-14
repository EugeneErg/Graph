<?php declare(strict_types = 1);
namespace EugeneErg\Graph\ValueObjects;

use EugeneErg\Graph\Collections\IntegerCollection;
use EugeneErg\Graph\Collections\IntegerMatrix;
use EugeneErg\Graph\Services\Assert\Argument;
use EugeneErg\Graph\Services\AssertService;
use EugeneErg\Graph\Traits\AttributeTrait;

class Graph extends AbstractGraph
{
    use AttributeTrait;

    public function __construct(IntegerMatrix $connections, ?IntegerCollection $vertexes = null)
    {
        $realVertex = IntegerCollection::fromRecursive($connections, fn ($value) => $value, true)->keys();

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

        $this->setConnections($connections);
        $this->setVertexes($vertexes ?? $realVertex);
    }

    public function createSupGraph(IntegerCollection $vertexes): Graph
    {
        $connections = new IntegerMatrix();

        foreach ($vertexes->listBy($vertexes, 1) as [$vertexA, $vertexB]) {
            if ($this->issetCell($vertexA->key, $vertexB->key)) {
                $connections->setCell($vertexA->key, $vertexB->key, $this->getCell($vertexA->key, $vertexB->key));
            }
        }

        return new static($connections, $vertexes->values());
    }

    public function direct(int $vertex): Graph
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
}
