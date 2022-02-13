<?php declare(strict_types=1);
namespace EugeneErg\Graph\Processes\Actions;

use EugeneErg\Graph\Collections\IntegerCollection;
use EugeneErg\Graph\Collections\IntegerMatrix;
use EugeneErg\Graph\Processes\Collections\ActionCollection;

/**
 * @method ActionCollection|MoveDisconnectedSubGraphAction[] getChildren()
 */
class CreateNewGraphAction extends AbstractAction
{
    private IntegerCollection $vertexes;
    private IntegerMatrix $connections;

    public function __construct(IntegerCollection $vertexes, IntegerMatrix $connections)
    {
        $this->vertexes = $vertexes;
        $this->connections = $connections;
        parent::__construct();
    }

    public function getConnections(): IntegerMatrix
    {
        return $this->connections;
    }

    public function getVertexes(): IntegerCollection
    {
        return $this->vertexes;
    }

    public function __debugInfo(): array
    {
        return array_replace(parent::__debugInfo(), [
            'vertexes' => $this->vertexes,
            'connections' => $this->connections,
        ]);
    }
}
