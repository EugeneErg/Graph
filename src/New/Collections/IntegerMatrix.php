<?php

declare(strict_types=1);

namespace EugeneErg\Graph\New\Collections;

use EugeneErg\Collections\CollectionCollection;
use EugeneErg\Collections\IntegerCollection;

class IntegerMatrix extends CollectionCollection
{
    protected const VALUE_TYPE = IntegerCollection::class;
    protected const KEY_TYPE = 'is_int';
}