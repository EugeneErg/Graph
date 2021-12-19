<?php declare(strict_types = 1);
namespace EugeneErg\Graph\ValueObjects;

use Closure;
use EugeneErg\Graph\Collections\EdgeCollection;
use EugeneErg\Graph\Collections\EdgeMatrix;
use EugeneErg\Graph\Collections\IntegerCollection;
use EugeneErg\Graphs\TroubleTreeCollection;
use EugeneErg\Graphs\TroubleTreeMatrix;
use Exception;

/**
 * @see Trouble::getVertexes()
 * @property-read IntegerCollection $vertexes
 * @see Trouble::getFromVertex()
 * @property-read int $fromVertex
 * @see Trouble::getToVertex()
 * @property-read int $toVertex
 * @see Trouble::getFirstVertexes()
 * @property-read IntegerCollection $firstVertexes
 * @see Trouble::getTrees()
 * @property-read TroubleTreeCollection $trees
 * @see Trouble::getMainTree()
 * @property-read TroubleTree $mainTree
 * @see Trouble::getEdges()
 * @property-read Edge[] $edges
 */
class Trouble extends AbstractValueObject
{
    private IntegerCollection $firstVertexes;
    private TroubleTreeCollection $trees;
    private ?TroubleTree $mainTree;
    private EdgeCollection $edges;
    private IntegerCollection $vertexes;
    private int $fromVertex;
    private int $toVertex;

    public function __construct(IntegerCollection $vertexes, int $fromVertex, int $toVertex)
    {
        $this->firstVertexes = $vertexes;
        $this->trees = new TroubleTreeCollection();
        $this->mainTree = null;
        $this->edges = new EdgeCollection();
        $this->vertexes = $vertexes;
        $this->fromVertex = $fromVertex;
        $this->toVertex = $toVertex;
    }

    /** @throws Exception */
    public function embedded(Edge $edge, Replacement $replacement): void
    {
        $this->edges[] = $edge;

        if (
            $replacement->firstVertex === $this->fromVertex
            && $replacement->lastVertex === $this->toVertex
        ) {
            $this->firstVertexes = $this->vertexes;
            $this->vertexes = $replacement->vertexes;
            $tree = new TroubleTree(
                $edge,
                null,
                null,
                new EdgeCollection(),
                $replacement->vertexes
            );

            $this->trees = TroubleTreeCollection::fromFillKeys($replacement->vertexes, $tree);
            $this->mainTree = $tree;
        } else {
            $fromPosition = $this->vertexes->search($replacement->firstVertex, true);
            $this->vertexes->splice(
                $fromPosition,
                $replacement->length,
                $replacement->vertexes
            );

            if (!isset($this->trees[$replacement->firstVertex], $this->trees[$replacement->lastVertex])) {
                throw new Exception();
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

            for ($i = 1; $i < $replacement->vertexes->count() - 1; $i++) {
                $this->trees[$replacement->vertexes[$i]] = $tree;
            }
        }
    }

    public function getTrees(): TroubleTreeCollection
    {
        return $this->trees;
    }

    public function getFirstVertexes(): IntegerCollection
    {
        return $this->firstVertexes;
    }

    public function getMainTree(): TroubleTree
    {
        return $this->mainTree;
    }

    /*private function getPath(int $leftVertex, int $rightVertex): array
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
    }*/

    /** @throws Exception */
    public function getInnerEdges(int $leftVertex, int $rightVertex): EdgeCollection
    {
        return $this->getParentEdges($this->getParentTrees($leftVertex, $rightVertex));
    }

    private function getParentTrees(int $leftVertex, int $rightVertex): TroubleTreeCollection
    {
        $parents = new TroubleTreeMatrix();
        $this->mapTree(
            $leftVertex,
            $rightVertex,
            static function (
                IntegerCollection $vertexes,
                TroubleTree $tree,
                ?bool $onRight,
                TroubleTree $leftParentTree = null
            ) use ($leftVertex, $parents): void {
                $parentVertex = $leftParentTree === null ? null
                    : $leftParentTree->vertexes[$leftParentTree->vertexes->count() - 1];
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
                        $parents->setCollection(
                            null,
                            $tree->leftParents->getCollection($vertex)->slice($parentPosition + 1)
                        );
                    } elseif ($tree->leftParents->issetCollection($vertex)) {
                        $parents->setCollection(null, $tree->leftParents->getCollection($vertex));
                    }
                }
            }
        );

        return TroubleTreeCollection::fromMerge(false, ...$parents);
    }

    /** @throws Exception */
    private function getParentEdges(TroubleTreeCollection $parents): EdgeCollection
    {
        $result = new EdgeMatrix();

        while ($parent = $parents->shift()) {
            $result->setCollection(null, $parent->edges);
            $result->setItem(null, null, $parent->edge);

            if ($parent->leftParents->count()) {
                $parents->push(...TroubleTreeMatrix::fromMerge(false, ...$parent->leftParents));
            }
        }

        $result = EdgeCollection::fromMerge(false, ...$result);

        if ($result->unique()->count() !== $result->count()) {
            throw new Exception();
        }

        return $result;
    }

    private function mapTree(int $leftVertex, int $rightVertex, Closure $closure): void
    {
        $leftTree = $this->trees[$leftVertex];
        $rightTree = $this->trees[$rightVertex];
        $parentLeftTree = null;

        while ($leftTree !== $rightTree) {
            if ($leftTree->level > $rightTree->level) {
                $pos = $leftTree->vertexes->search($leftVertex, true);
                $closure($leftTree->vertexes->slice($pos, -1), $leftTree, false, $parentLeftTree);
                $leftVertex = $leftTree->vertexes[$leftTree->vertexes->count() - 1];
                $parentLeftTree = $leftTree;
                $leftTree = $leftTree->rightChild;
            } else {
                $pos = $rightTree->vertexes->search($rightVertex, true);
                $closure($rightTree->vertexes->slice(1, $pos), $rightTree, true, null);
                $rightVertex = $rightTree->vertexes[0];
                $rightTree = $rightTree->leftChild;
            }
        }

        $leftPos = $leftTree->vertexes->search($leftVertex, true);
        $rightPos = $rightTree->vertexes->search($rightVertex, true);
        $closure(
            $leftTree->vertexes->slice($leftPos, $rightPos - $leftPos + 1),
            $rightTree,
            null,
            $parentLeftTree
        );
    }

    protected function getEdges(): EdgeCollection
    {
        return $this->edges;
    }

    protected function getVertexes(): IntegerCollection
    {
        return $this->vertexes;
    }

    protected function getFromVertex(): int
    {
        return $this->fromVertex;
    }

    public function getToVertex(): int
    {
        return $this->toVertex;
    }
}
