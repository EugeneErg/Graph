<?php declare(strict_types=1);
namespace EugeneErg\Graph\Collections;

use EugeneErg\Graph\ValueObjects\TroubleTree;;

/**
 * @method TroubleTree shift()
 * @method TroubleTree offsetGet($offset)
 * @method TroubleTree[] getIterator()
 */
class TroubleTreeCollection extends AbstractLineCollection
{
    protected const ELEMENT_CLASS = TroubleTree::class;
}
