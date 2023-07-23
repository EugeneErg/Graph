<?php

declare(strict_types=1);

namespace EugeneErg\Graph\New\Animations\Collections;

use EugeneErg\Collections\ObjectCollection;
use EugeneErg\Graph\New\Animations\DataTransferObjects\Circle;

class VertexCollection extends ObjectCollection
{
    protected const VALUE_TYPE = Circle::class;
}