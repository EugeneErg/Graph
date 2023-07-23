<?php

declare(strict_types=1);

namespace EugeneErg\Graph\New\Events;

use EugeneErg\Collections\IntegerCollection;

class DisconnectedGraphFoundEvent implements EventInterface
{
    public function __construct(public readonly IntegerCollection $vertexes)
    {
    }
}