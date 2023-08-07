<?php

declare(strict_types=1);

namespace EugeneErg\Graph\New\Actions;

use EugeneErg\Graph\New\Collections\ActionCollection;
use EugeneErg\Graph\New\Collections\AnimationGraphCollection;
use EugeneErg\Graph\New\DataTransferObjects\AnimationGraph;

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

    abstract public function drawGraph(
        AnimationGraph $parentGraph,
        AnimationGraphCollection $graphs,
        int $startMilliseconds,
    ): array;
}
