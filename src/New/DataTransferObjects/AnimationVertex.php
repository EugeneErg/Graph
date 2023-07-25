<?php

declare(strict_types=1);

namespace EugeneErg\Graph\New\DataTransferObjects;

use EugeneErg\Graph\New\Animations\Collections\LineCollection;
use EugeneErg\Graph\New\Animations\DataTransferObjects\Circle;

class AnimationVertex
{
    public function __construct(
        public readonly Circle $circle,
        public readonly LineCollection $connections,
    ) {
    }
}
