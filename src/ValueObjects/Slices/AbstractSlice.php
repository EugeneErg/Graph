<?php declare(strict_types=1);
namespace EugeneErg\Graph\ValueObjects\Slices;

use EugeneErg\Graph\Collections\AbstractCollection2;
use EugeneErg\Graph\Collections\IntegerCollection;
use EugeneErg\Graph\Collections\IntegerMatrix;
use EugeneErg\Graph\ValueObjects\AbstractValueObject;

abstract class AbstractSlice extends AbstractValueObject
{
    private IntegerCollection $steps;
    private IntegerMatrix $tree;
    private int $position = 0;

    public function __construct(?IntegerCollection $steps = null)
    {
        $this->steps = $steps === null ? new IntegerCollection() : $steps->values();
        $this->tree = new IntegerMatrix();
    }

    final public function nextKey(AbstractCollection2 $collection): int
    {
        $this->tree->setCollection(null, IntegerCollection::fromKeys($collection));

        if ($this->position >= $this->steps->count()) {
            $result = $this->getKeyByCollection($collection);
            $this->steps[] = $result;
            $this->position++;

            return $result;
        }

        $result = $this->steps[$this->position++];

        if (!$collection->isset($result)) {
            throw new \Error();
        }

        return $result;
    }

    final public function getTree(): IntegerMatrix
    {
        return $this->tree;
    }

    final public function getSteps(): IntegerCollection
    {
        return $this->steps;
    }

    abstract protected function getKeyByCollection(AbstractCollection2 $collection): int;
}
