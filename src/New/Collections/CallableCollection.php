<?php

declare(strict_types=1);

namespace EugeneErg\Graph\New\Collections;

use EugeneErg\Collections\MixedCollection;

class CallableCollection extends MixedCollection
{
    protected const VALUE_TYPE = 'is_callable';
}
