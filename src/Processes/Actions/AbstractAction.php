<?php declare(strict_types=1);

namespace EugeneErg\Graph\Processes\Actions;

use EugeneErg\Graph\Processes\Collections\ActionCollection;

abstract class AbstractAction
{
    private ActionCollection $children;
    private ?AbstractAction $parent;

    public function __construct(?AbstractAction $parent = null)
    {
        $this->children = new ActionCollection();
        $this->parent = $parent;

        if ($parent !== null) {
            $parent->children[] = $this;
        }
    }

    public function getChildren(): ActionCollection
    {
        return $this->children;
    }

    public function getChild(int $number): AbstractAction
    {
        return $this->children[$number];
    }

    public function getParent(): AbstractAction
    {
        return $this->parent;
    }

    public function __debugInfo(): array
    {
        return [
            'children' => $this->children,
        ];
    }
}
