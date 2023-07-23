<?php declare(strict_types=1);

namespace EugeneErg\Graph\Processes\Collections;

use EugeneErg\Graph\Collections\AbstractLineCollection;
use EugeneErg\Graph\Processes\SvgAnimation\ValueObjects\GroupState;

/**
 * @method GroupState[] getIterator()
 */
class GroupStateCollection extends AbstractLineCollection
{
    protected const ELEMENT_CLASS = GroupState::class;

    //public function
}
