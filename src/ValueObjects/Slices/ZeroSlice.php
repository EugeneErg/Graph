<?php declare(strict_types=1);

namespace EugeneErg\Graph\ValueObjects\Slices;

use EugeneErg\Graph\Collections\AbstractCollection2;

class ZeroSlice extends AbstractSlice
{
    protected function getKeyByCollection(AbstractCollection2 $collection): int
    {
        return $collection->getKeyByPosition(0);
    }
}
