<?php declare(strict_types = 1);
namespace EugeneErg\Graph\ValueObjects;

use EugeneErg\Graph\Collections\EdgeCollection;
use EugeneErg\Graph\Collections\IntegerCollection;

/**
 * @see Edge::getVertexes()
 * @property-read IntegerCollection $vertexes
 * @see Edge::getChildren()
 * @property-read EdgeCollection $children
 * @see Edge::getParentAttribute()
 * @property-read Edge $parent
 */
class Edge extends AbstractValueObject
{
    private $parent = null;
    private $vertexes;
    private $children;

    public function __construct(IntegerCollection $vertexes, ?EdgeCollection $children = null)
    {
        $this->vertexes = $vertexes;
        $this->children = $children;
        ($children ?? new EdgeCollection())->foreach(function (Edge $child) {
            $child->parent = $this;
        });
    }

    public function getNormalVertexNumber(int $offset): int
    {
        $count = $this->vertexes->count();

        return ($offset < 0 && $offset !== - $count ? $count : 0) + ($offset % $count);
    }

    public function getVertex(int $offset): int
    {
        return $this->vertexes[$this->getNormalVertexNumber($offset)];
    }

    public function getVertexes(int $offset = 0, int $count = null): IntegerCollection
    {
        $result = new IntegerCollection();
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
        return $this->vertexes->search($vertex, true);
    }

    public function replace(IntegerCollection $vertexes, int $start, int $length): Edge
    {
        return new Edge(
            $this->getVertexes($length + $start, count($this->vertexes) - $length)->merge($vertexes)
        );
    }

    protected function getParent(): ?self
    {
        return $this->parent;
    }

    public function getChildren(): EdgeCollection
    {
        return $this->children;
    }

    public function toArray(): array
    {
        return $this->vertexes->toArray();
    }
}