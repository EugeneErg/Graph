<?php declare(strict_types=1);
namespace EugeneErg\Graph\ValueObjects\Slices;

use EugeneErg\Graph\Collections\AbstractCollection2;
use EugeneErg\Graph\Collections\IntegerCollection;
use EugeneErg\Graph\ValueObjects\AbstractValueObject;

abstract class AbstractSlice extends AbstractValueObject
{
    private IntegerCollection $steps;
    private IntegerCollection $tree;
    private int $position = 0;

    public function __construct(?IntegerCollection $steps = null)
    {
        $this->steps = $steps === null ? new IntegerCollection() : $steps->values();
        $this->tree = new IntegerCollection();
    }

    final public function nextKey(AbstractCollection2 $collection): int
    {
        $this->tree[] = $collection->count();

        if ($this->position >= $this->steps->count()) {
            $this->steps[] = $result = $this->getKeyPositionByCollection($collection);
        } else {
            $result = $this->steps[$this->position];
        }

        $this->position++;

        if ($collection->count() <= $result) {
            throw new \Error();
        }

        return $collection->getKeyByPosition($result);
    }

    final public function getTree(): IntegerCollection
    {
        return $this->tree;
    }

    final public function getSteps(): IntegerCollection
    {
        return $this->steps;
    }

    abstract protected function getKeyPositionByCollection(AbstractCollection2 $collection): int;
}
