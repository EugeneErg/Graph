<?php declare(strict_types = 1);
namespace EugeneErg\Graph\Processes\Collections;

use EugeneErg\Graph\Collections\AbstractLineCollection;
use EugeneErg\Graph\Processes\Actions\AbstractAction;

class ActionCollection extends AbstractLineCollection
{
    protected const ELEMENT_CLASS = AbstractAction::class;
}
