<?php declare(strict_types = 1);
namespace EugeneErg\Graph\ValueObjects;

/**
 * @property-read Edge $edge
 * @property-read TroubleTree|null $leftChild
 * @property-read TroubleTree|null $rightChild
 * @property-read Edge[] $edges
 * @property-read int[] $vertexes
 * @see TroubleTree::getLevelAttribute()
 * @property-read int $level
 * @see TroubleTree::getLeftParentsAttribute()
 * @property-read TroubleTree[][] $leftParents
 */
class TroubleTree extends AbstractValueObjectMutable
{
    private $leftParents;
    private $rightParents;
    private $level;

    /**
     * @param Edge $edge
     * @param TroubleTree|null $leftChild
     * @param TroubleTree|null $rightChild
     * @param Edge[] $edges
     * @param int[] $vertexes
     */
    public function __construct(
        Edge $edge,
        ?TroubleTree $leftChild,
        ?TroubleTree $rightChild,
        array $edges,
        array $vertexes
    ) {
        if ($leftChild !== null) {
            $leftChild->rightParents[$vertexes[0]][] = $this;
        }

        if ($rightChild !== null) {
            $rightChild->leftParents[end($vertexes)][] = $this;
        }

        $this->leftParents = [];
        $this->rightParents = [];
        $this->level = max($leftChild->level ?? -1, $rightChild->level ?? -1) + 1;

        parent::__construct($edge, $leftChild, $rightChild, $edges, $vertexes);
    }

    /**
     * @param int $vertex
     * @param bool $onRight
     * @return int[]|null
     */
    public function findPath(int $vertex, bool $onRight): ?array
    {
        $tree = $this;
        $result = [];

        while ($tree !== null) {
            $pos = array_search($vertex, $tree->vertexes, true);

            if ($pos === false) {
                return null;
            }

            $item = array_slice(
                $tree->vertexes,
                $onRight ? $pos + 1 : 0,
                $onRight ? null : $pos
            );
            $onRight ? $result[] = $item : array_unshift($result, $item);
            $vertex = $tree->vertexes[$onRight ? count($tree->vertexes) - 1 : 0];
            $tree = $onRight ? $tree->rightChild : $tree->leftChild;
        }

        return array_merge(...$result);
    }

    /** @return Edge[] */
    public function getAllEdges(): array
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
    }

    public function getLevelAttribute(): int
    {
        return $this->level;
    }

    public function getLeftParentsAttribute(): array
    {
        return $this->leftParents;
    }

    public function toArray(): array
    {
        $result = parent::toArray();
        unset($result['leftParents'], $result['rightParents']);

        return $result;
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
}