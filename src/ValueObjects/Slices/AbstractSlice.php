<?php declare(strict_types=1);
namespace EugeneErg\Graph\ValueObjects\Slices;

use EugeneErg\Graph\Collections\AbstractCollection2;
use EugeneErg\Graph\Collections\IntegerCollection;
use EugeneErg\Graph\Collections\IntegerMatrix;
use EugeneErg\Graph\ValueObjects\AbstractValueObject;

abstract class AbstractSlice extends AbstractValueObject
{
    private IntegerCollection $steps;
    private IntegerCollection $path;
    private IntegerMatrix $tree;

    public function __construct(?IntegerCollection $steps = null)
    {
        $this->steps = $steps ?? new IntegerCollection();
        $this->path = clone $steps;
        $this->tree = new IntegerMatrix();
    }

    final public function nextKey(AbstractCollection2 $collection): int
    {
        $this->tree->setCollection(null, IntegerCollection::fromKeys($collection));

        if ($this->steps->isEmpty()) {
            $result = $this->getKeyByCollection($collection);
            $this->path[] = $result;

            return $result;
        }

        $result = $this->steps->shift();

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
        return $this->path;
    }

    abstract protected function getKeyByCollection(AbstractCollection2 $collection): int;
}
