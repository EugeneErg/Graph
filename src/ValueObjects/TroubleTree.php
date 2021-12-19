<?php declare(strict_types = 1);
namespace EugeneErg\Graph\ValueObjects;

use EugeneErg\Graph\Collections\EdgeCollection;
use EugeneErg\Graph\Collections\IntegerCollection;
use EugeneErg\Graph\Collections\IntegerMatrix;
use EugeneErg\Graphs\TroubleTreeMatrix;

/**
 * @see TroubleTree::getEdge()
 * @property-read Edge $edge
 * @see TroubleTree::getLeftChild()
 * @property-read TroubleTree|null $leftChild
 * @see TroubleTree::getRightChild()
 * @property-read TroubleTree|null $rightChild
 * @see TroubleTree::getEdges()
 * @property-read EdgeCollection $edges
 * @see TroubleTree::getVertexes()
 * @property-read IntegerCollection $vertexes
 * @see TroubleTree::getLevel()
 * @property-read int $level
 * @see TroubleTree::getLeftParents()
 * @property-read TroubleTreeMatrix $leftParents
 */
class TroubleTree extends AbstractValueObject
{
    private TroubleTreeMatrix $leftParents;
    private TroubleTreeMatrix $rightParents;
    private int $level;
    private Edge $edge;
    private ?TroubleTree $leftChild;
    private ?TroubleTree $rightChild;
    private EdgeCollection $edges;
    private IntegerCollection $vertexes;

    public function __construct(
        Edge $edge,
        ?TroubleTree $leftChild,
        ?TroubleTree $rightChild,
        EdgeCollection $edges,
        IntegerCollection $vertexes
    ) {
        if ($leftChild !== null) {
            $leftChild->rightParents->setItem($vertexes->getValueByPosition(0), null, $this);
        }

        if ($rightChild !== null) {
            $rightChild->leftParents->setItem($vertexes->getValueByPosition(), null, $this);
        }

        $this->leftParents = new TroubleTreeMatrix();
        $this->rightParents = new TroubleTreeMatrix();
        $this->level = max($leftChild->level ?? -1, $rightChild->level ?? -1) + 1;
        $this->edge = $edge;
        $this->leftChild = $leftChild;
        $this->rightChild = $rightChild;
        $this->edges = $edges;
        $this->vertexes = $vertexes;
    }

    public function findPath(int $vertex, bool $onRight): ?IntegerCollection
    {
        $tree = $this;
        $result = new IntegerMatrix();

        while ($tree !== null) {
            $pos = $tree->vertexes->search($vertex, true);

            if ($pos === false) {
                return null;
            }

            $item = $tree->vertexes->slice(
                $onRight ? $pos + 1 : 0,
                $onRight ? null : $pos
            );
            $onRight ? $result->setCollection(null, $item) : $result->unshift($item);
            $vertex = $tree->vertexes[$onRight ? $tree->vertexes->count() - 1 : 0];
            $tree = $onRight ? $tree->rightChild : $tree->leftChild;
        }

        return IntegerCollection::fromMerge(false, ...$result);
    }

    /*public function getAllEdges(): EdgeCollection
    {
        $result = [];
        $trees = [$this];

        for ($i = 0; $i < count($trees); $i++) {
            $tree = $trees[$i];
            $result[] = $tree->edge;

            if ($tree->leftChild) {
                $trees[] = $tree->leftChild;
            }

            if ($tree->rightChild) {
                $trees[] = $tree->rightChild;
            }

            $trees = array_merge($trees, $this->middleChildren);
        }

        return $result;
    }*/

    public function getLevel(): int
    {
        return $this->level;
    }

    public function getLeftParents(): TroubleTreeMatrix
    {
        return $this->leftParents;
    }

    public function toArray(): array
    {
        return [

        ];
    }

    public function removeLeftParent(self $parent): void
    {
        foreach ($this->leftParents as $pos1 => $parents) {
            $pos2 = array_search($parent, $parents, true);

            if ($pos2 !== false) {
                unset($this->leftParents[$pos1][$pos2]);
            }
        }
    }

    protected function getEdge(): Edge
    {
        return $this->edge;
    }

    protected function getVertexes(): IntegerCollection
    {
        return $this->vertexes;
    }

    protected function getEdges(): EdgeCollection
    {
        return $this->edges;
    }

    protected function getLeftChild(): ?TroubleTree
    {
        return $this->leftChild;
    }

    protected function getRightChild(): ?TroubleTree
    {
        return $this->rightChild;
    }
}