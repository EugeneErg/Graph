<?php

declare(strict_types=1);

namespace EugeneErg\Graph\New\Animations\Collections;

use EugeneErg\Collections\ObjectCollection;
use EugeneErg\Graph\New\Animations\DataTransferObjects\Line;

class ConnectionCollection extends ObjectCollection
{
    protected const VALUE_TYPE = Line::class;
}