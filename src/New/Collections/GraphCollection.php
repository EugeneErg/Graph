<?php

declare(strict_types=1);

namespace EugeneErg\Graph\New\Collections;

use EugeneErg\Collections\ObjectCollection;
use EugeneErg\Graph\New\ValueObjects\Graph;

class GraphCollection extends ObjectCollection
{
    protected const VALUE_TYPE = Graph::class;
}