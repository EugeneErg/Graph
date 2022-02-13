<?php declare(strict_types=1);
namespace EugeneErg\Graph\Processes\Actions;

use EugeneErg\Graph\Collections\IntegerCollection;
use EugeneErg\Graph\Processes\Collections\ActionCollection;

/**
 * @method ActionCollection|MoveConnectedGraphAction[] getChildren()
 */
class SelectArticulationVertexesAction extends AbstractAction
{
    private IntegerCollection $vertexes;

    public function __construct(IntegerCollection $vertexes, MoveDisconnectedSubGraphAction $parentAction)
    {
        $this->vertexes = $vertexes;
        parent::__construct($parentAction);
    }

    public function getVertexes(): IntegerCollection
    {
        return $this->vertexes;
    }

    public function __debugInfo(): array
    {
        return array_replace(parent::__debugInfo(), [
            'vertexes' => $this->vertexes,
        ]);
    }
}
