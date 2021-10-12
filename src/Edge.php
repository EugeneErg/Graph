<?php namespace EugeneErg\Graphs;

class Edge
{
    /** @var Vertex[] */
    private $vertexes;
    /** @var Edge[] */
    private $children;
    /** @var Edge */
    private $parent;

    /**
     * Edge constructor.
     * @param Vertex[] $vertexes
     * @param Edge[] $children
     */
    public function __construct(array $vertexes, array $children = [])
    {
        $this->vertexes = $vertexes;
        $this->children = $children;

        foreach ($this->children as $child) {
            $child->parent = $this;
        }
    }

    /**
     * @return Vertex[]
     */
    public function getVertexes(): array
    {
        return $this->vertexes;
    }

    /**
     * @return Edge[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * @return self[]
     */
    public function asList(): array
    {
        $result = [$this];

        for ($i = 0; $i < count($result); $i++) {
            foreach ($result[$i]->children as $child) {
                $result[] = $child;
            }
        }

        return $result;
    }

    public function getLastVertex(): int
    {
        return end($this->vertexes);
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }
}