<?php

declare(strict_types=1);

namespace EugeneErg\Graph\New\Collections;

use EugeneErg\Collections\ObjectCollection;
use EugeneErg\Graph\New\ValueObjects\Tree;

class TreeCollection extends ObjectCollection
{
    protected const VALUE_TYPE = Tree::class;
}
