<?php declare(strict_types=1);
namespace EugeneErg\Graph\Collections;

use EugeneErg\Graph\Collections\AbstractLineCollection;
use EugeneErg\Graph\ValueObjects\TroubleTree;

/**
 * @method TroubleTree shift()
 * @method TroubleTree offsetGet($offset)
 */
class TroubleTreeCollection extends AbstractLineCollection
{
    protected const ELEMENT_CLASS = TroubleTree::class;
}
