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
 */
class Graph extends IntegerMatrix
{
    /** @var IntegerCollection  */
    private $vertexes;

    public function __construct(IntegerMatrix $connections, ?IntegerCollection $vertexes = null)
    {
        parent::__construct($connections->toArray());
        $realVertex = IntegerCollection::fromKeys(
            Collection::fromReplace(false, $this, ...$this)
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

        foreach ($vertexes->listBy($vertexes, 1) as [$vertexA, $vertexB]) {
            if (isset($this[$vertexA->key][$vertexB->key])) {
                $connections->set([(int) $vertexA->key, (int) $vertexB->key], $this[$vertexA->key][$vertexB->key]);
            }
        }

        return new static($connections, $vertexes->values());
    }

    public function direct(int $vertex): Graph
    {
        $parent = null;
        $new = clone $this;

        for ($vertexes = [$vertex => $parent]; $vertex !== null; $vertex = key($vertexes)) {
            foreach ($this[$vertex] ?? [] as $vertexB => $value) {
                if ($vertexB !== $parent) {
                    unset($new[$vertexB][$vertex]);
                    $vertexes[$vertexB] = $vertex;
                }
            }

            $parent = next($vertexes);
        }

        return $new;
    }

    public function getVertexes(): IntegerCollection
    {
        return $this->vertexes;
    }

    public function toArray(): array
    {
        return [
            'matrix' => parent::toArray(),
            'vertexes' => $this->vertexes,
        ];
    }
}
