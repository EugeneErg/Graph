<?php

declare(strict_types=1);

namespace EugeneErg\Graph\New\DataTransferObjects;

use EugeneErg\Graph\New\Collections\AnimationVertexCollection;

class AnimationGraph
{
    public function __construct(public readonly AnimationVertexCollection $vertexes)
    {
    }
}
