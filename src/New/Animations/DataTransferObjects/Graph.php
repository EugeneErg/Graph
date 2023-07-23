<?php

declare(strict_types=1);

namespace EugeneErg\Graph\New\Animations\DataTransferObjects;

use EugeneErg\Graph\New\Animations\Collections\ConnectionCollection;
use EugeneErg\Graph\New\Animations\Collections\VertexCollection;

class Graph
{
    public function __construct(
        public readonly VertexCollection     $vertexes,
        public readonly ConnectionCollection $connections,
    ) {
    }
}
