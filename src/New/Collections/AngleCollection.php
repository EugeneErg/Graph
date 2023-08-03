<?php

declare(strict_types=1);

namespace EugeneErg\Graph\New\Collections;

use EugeneErg\Collections\ObjectCollection;
use EugeneErg\Graph\New\ValueObjects\Angle;

class AngleCollection extends ObjectCollection
{
    protected const VALUE_TYPE = Angle::class;
}