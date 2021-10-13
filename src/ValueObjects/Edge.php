<?php declare(strict_types = 1);
namespace EugeneErg\Graph\ValueObjects;

/**
 * @property-read int[] $vertexes
 * @property-read Edge[] $children
 * @see Edge::getParentAttribute()
 * @property-read Edge $parent
 */
class Edge extends AbstractValueObjectMutable
{
    private $parent = null;

    /**
     * Edge constructor.
     * @param int[] $vertexes
     * @param self[] $children
     */
    public function __construct(array $vertexes, array $children = [])
    {
        parent::__construct($vertexes, $children);

        foreach ($children as $child) {
            $child->parent = $this;
        }
    }

    public function getNormalVertexNumber(int $offset): int
    {
        $count = count($this->vertexes);

        return ($offset < 0 && $offset !== - $count ? $count : 0) + ($offset % $count);
    }

    public function getVertex(int $offset): int
    {
        return $this->vertexes[$this->getNormalVertexNumber($offset)];
    }

    public function getVertexes(int $offset, int $count = null): array
    {
        $result = [];
        $count = $count ?? count($this->vertexes);

        if ($count < 0) {
            for ($i = 0; $i > $count; $i--) {
                $result[] = $this->getVertex($i + $offset);
            }
        } else {
            for ($i = 0; $i < $count; $i++) {
                $result[] = $this->getVertex($i + $offset);
            }
        }

        return $result;
    }


    public function findVertex(int $vertex): ?int
    {
        $result = array_search($vertex, $this->vertexes, true);

        return $result === false ? null : $result;
    }

    public function replace(array $vertexes, int $start, int $length): Edge
    {
        return new Edge(array_merge(
            $this->getVertexes($length + $start, count($this->vertexes) - $length),
            $vertexes
        ));
    }

    protected function getParentAttribute(): ?self
    {
        return $this->parent;
    }
}