<?php

declare(strict_types=1);

namespace EugeneErg\Graph\New\Collections;

use EugeneErg\Collections\ObjectCollection;
use EugeneErg\Graph\New\DataTransferObjects\AnimationGraph;

/**
 * @method AnimationGraph[] getIterator()
 * @method AnimationGraph offsetGet(mixed $offset)
 */
class AnimationGraphCollection extends ObjectCollection
{
    protected const VALUE_TYPE = AnimationGraph::class;
}