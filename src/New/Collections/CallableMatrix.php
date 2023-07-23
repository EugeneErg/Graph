<?php

declare(strict_types=1);

namespace EugeneErg\Graph\New\Collections;

use EugeneErg\Collections\CollectionCollection;

class CallableMatrix extends CollectionCollection
{
    protected const VALUE_TYPE = CallableCollection::class;
}