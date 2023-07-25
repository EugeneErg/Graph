<?php

declare(strict_types=1);

namespace EugeneErg\Graph\New\Collections;

use EugeneErg\Collections\ObjectCollection;
use EugeneErg\Graph\New\Actions\AbstractAction;

class ActionCollection extends ObjectCollection
{
    protected const VALUE_TYPE = AbstractAction::class;
}