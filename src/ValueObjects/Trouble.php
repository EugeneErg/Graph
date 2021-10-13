<?php namespace EugeneErg\Graph\ValueObjects;

/**
 * @property-read int[] $vertexes
 * @property-read int $fromVertex
 * @property-read int $toVertex
 * @see Trouble::getFirstVertexesAttribute()
 * @property-read int[] $firstVertexes
 * @see Trouble::getTreesAttribute()
 * @property-read TroubleTree[] $trees
 * @see Trouble::getMainTreeAttribute()
 * @property-read TroubleTree $mainTree
 * @see Trouble::getEdgesAttribute()
 * @property-read Edge[] $edges
 */
class Trouble extends AbstractValueObjectMutable
{
    private $firstVertexes;
    private $trees;
    private $mainTree;
    private $edges = [];

    public function __construct(array $vertexes, int $fromVertex, int $toVertex)
    {
        $this->firstVertexes = $vertexes;
        $this->trees = [];
        $this->mainTree = null;
        $this->edges = [];
        parent::__construct($vertexes, $fromVertex, $toVertex);
    }

    public function embedded(Edge $edge, Replacement $replacement): void
    {
        $attributes = $this->getAttributes();
        $this->edges[] = $edge;

        if (
            $replacement->firstVertex === $this->fromVertex
            && $replacement->lastVertex === $this->toVertex
        ) {
            $attributes->firstVertexes = $attributes->vertexes;
            $attributes->vertexes = $replacement->vertexes;
            $tree = new TroubleTree(
                $edge,
                null,
                null,
                [],
                $replacement->vertexes
            );
            $this->trees = array_fill_keys($replacement->vertexes, $tree);
            $this->mainTree = $tree;
        } else {
            $fromPosition = array_search($replacement->firstVertex, $attributes->vertexes, true);
            array_splice(
                $attributes->vertexes,
                $fromPosition,
                $replacement->length,
                $replacement->vertexes
            );

            if (!isset($this->trees[$replacement->firstVertex], $this->trees[$replacement->lastVertex])) {
                throw new \Exception();
            }

            $parents = $this->getParentTrees($replacement->firstVertex, $replacement->lastVertex);

            foreach ($parents as $parent) {
                $parent->rightChild->removeLeftParent($parent);
            }

            $tree = new TroubleTree(
                $edge,
                $this->trees[$replacement->firstVertex],
                $this->trees[$replacement->lastVertex],
                $this->getParentEdges($parents),
                $replacement->vertexes
            );

            for ($i = 1; $i < count($replacement->vertexes) - 1; $i++) {
                $this->trees[$replacement->vertexes[$i]] = $tree;
            }
        }
    }

    public function getTreesAttribute(): array
    {
        return $this->trees;
    }

    public function getFirstVertexesAttribute(): array
    {
        return $this->firstVertexes;
    }

    public function getMainTreeAttribute(): TroubleTree
    {
        return $this->mainTree;
    }

    private function getPath(int $leftVertex, int $rightVertex): array
    {
        $leftResult = $rightResult = [];
        $this->mapTree(
            $leftVertex,
            $rightVertex,
            static function(array $vertexes, TroubleTree $tree, ?bool $onRight)
            use (&$leftResult, &$rightResult): void {
                if ($onRight) {
                    array_push($rightResult, $vertexes);
                } else {
                    $leftResult[] = $vertexes;
                }
            }
        );

        return array_merge(...$leftResult, ...$rightResult);
    }

    public function getInnerEdges(int $leftVertex, int $rightVertex): array
    {
        return $this->getParentEdges($this->getParentTrees($leftVertex, $rightVertex));
    }

    /**
     * @param int $leftVertex
     * @param int $rightVertex
     * @return TroubleTree[]
     */
    private function getParentTrees(int $leftVertex, int $rightVertex): array
    {
        $parents = [];
        $this->mapTree(
            $leftVertex,
            $rightVertex,
            static function (array $vertexes, TroubleTree $tree, ?bool $onRight, TroubleTree $leftParentTree = null)
            use ($leftVertex, &$parents): void {
                $parentVertex = $leftParentTree === null ? null
                    : $leftParentTree->vertexes[count($leftParentTree->vertexes) - 1];
                $parentPosition = $leftParentTree === null ? null : array_search(
                    $leftParentTree,
                    $tree->leftParents[$parentVertex],
                    true
                );

                foreach ($vertexes as $vertex) {
                    if ($vertex === $leftVertex) {
                        continue;
                    }

                    if ($vertex === $parentVertex) {
                        $parents[] = array_slice($tree->leftParents[$vertex], $parentPosition + 1);
                    } elseif (isset($tree->leftParents[$vertex])) {
                        $parents[] = $tree->leftParents[$vertex];
                    }
                }
            }
        );

        return count($parents) ? array_merge(...$parents) : [];
    }

    private function getParentEdges(array $parents): array
    {
        $result = [];

        /** @var TroubleTree $parent */
        while ($parent = array_shift($parents)) {
            $result[] = $parent->edges;
            $result[] = [$parent->edge];

            if (count($parent->leftParents)) {
                array_push($parents, ...array_merge(...$parent->leftParents));
            }
        }

        $result = count($result) ? array_merge(...$result) : [];

        if (count(array_unique($result)) !== count($result)) {
            throw new \Exception();
        }

        return $result;
    }

    private function mapTree(int $leftVertex, int $rightVertex, \Closure $closure): void
    {
        $leftTree = $this->trees[$leftVertex];
        $rightTree = $this->trees[$rightVertex];
        $parentLeftTree = null;

        while ($leftTree !== $rightTree) {
            if ($leftTree->level > $rightTree->level) {
                $pos = array_search($leftVertex, $leftTree->vertexes, true);
                $closure(array_slice($leftTree->vertexes, $pos, -1), $leftTree, false, $parentLeftTree);
                $leftVertex = $leftTree->vertexes[count($leftTree->vertexes) - 1];
                $parentLeftTree = $leftTree;
                $leftTree = $leftTree->rightChild;
            } else {
                $pos = array_search($rightVertex, $rightTree->vertexes, true);
                $closure(array_slice($rightTree->vertexes, 1, $pos), $rightTree, true, null);
                $rightVertex = $rightTree->vertexes[0];
                $rightTree = $rightTree->leftChild;
            }
        }

        $leftPos = array_search($leftVertex, $leftTree->vertexes, true);
        $rightPos = array_search($rightVertex, $rightTree->vertexes, true);
        $closure(
            array_slice($leftTree->vertexes, $leftPos, $rightPos - $leftPos + 1),
            $rightTree,
            null,
            $parentLeftTree
        );
    }

    protected function getEdgesAttribute(): array
    {
        return $this->edges;
    }
}
