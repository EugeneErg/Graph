<?php

declare(strict_types=1);

namespace EugeneErg\Graph\New\Collections;

use EugeneErg\Collections\ObjectCollection;
use EugeneErg\Graph\New\DataTransferObjects\AnimationGraph;

class AnimationGraphCollection extends ObjectCollection
{
    protected const VALUE_TYPE = AnimationGraph::class;
}