<?php declare(strict_types = 1);
namespace EugeneErg\Graph\ValueObjects\Temp;

use EugeneErg\Graph\ValueObjects\Edge;

/**
 * @see Problem::__get()
 * @see Problem::__set()
 * @property int[] $vertexesWithSide
 */
class Problem extends AbstractTempDto
{
    public $beginVertex;
    public $endVertex;
    /** @var int[] */
    public $vertexes;
    /** @var int[] */
    public $firstVertexes;
    /** @var Combine[] */
    public $combines;
    /** @var Edge[] */
    public $graph;
    /** @var Edge[] */
    public $edges;
    
    public function __construct(int $begin, int $end)
    {
        $this->beginVertex = $begin;
        $this->endVertex = $end;
        $this->vertexes = [];
        $this->firstVertexes = [];
        $this->combines = [];
        $this->graph = [];
        $this->edges = [];
    }

    public function __get(string $name)
    {
        if ($name === 'vertexesWithSide') {
            return array_merge([$this->beginVertex], $this->vertexes, [$this->endVertex]);
        }

        parent::__get($name);
    }

    public function __set(string $name, $value): void
    {
        if ($name === 'vertexesWithSide') {
            array_shift($value);
            array_pop($value);
            $this->vertexes = $value;

            return;
        }

        parent::__set($name, $value);
    }
}