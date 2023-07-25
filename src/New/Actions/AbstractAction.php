<?php

declare(strict_types=1);

namespace EugeneErg\Graph\New\Actions;

use EugeneErg\Graph\New\Collections\ActionCollection;

abstract class AbstractAction
{
    public readonly ActionCollection $children;

    public function __construct(public readonly ?AbstractAction $parent = null)
    {
        $this->children = new ActionCollection(immutable: false);

        if ($parent !== null) {
            $parent->children[] = $this;
        }
    }
}
