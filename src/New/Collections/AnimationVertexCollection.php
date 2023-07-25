<?php

declare(strict_types=1);

namespace EugeneErg\Graph\New\Collections;

use EugeneErg\Collections\ObjectCollection;
use EugeneErg\Graph\New\DataTransferObjects\AnimationVertex;

/**
 * @method AnimationVertex offsetGet(mixed $offset)
 */
class AnimationVertexCollection extends ObjectCollection
{
    protected const VALUE_TYPE = AnimationVertex::class;
}