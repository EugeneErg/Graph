<?php declare(strict_types=1);
namespace EugeneErg\Graph\Processes\SvgAnimation\ValueObject;

use EugeneErg\Graph\Collections\IntegerMatrix;
use EugeneErg\Graph\Collections\Point2DCollection;
use EugeneErg\Graph\Processes\Collections\Point2DGraphCollection;
use EugeneErg\Graph\ValueObjects\AbstractValueObject;

/**
 * @see Point2DGraph::getVertexes()
 * @property-read Point2DCollection $vertexes
 * @see Point2DGraph::getConnections()
 * @property-read IntegerMatrix $connections
 * @see Point2DGraph::getParent()
 * @property-read Point2DGraph|null $parent
 * @see Point2DGraph::getChildren()
 * @property-read Point2DGraphCollection $children
 */
class Point2DGraph extends AbstractValueObject
{
    private Point2DCollection $vertexes;
    private IntegerMatrix $connections;
    private ?Point2DGraph $parent;
    private Point2DGraphCollection $children;

    public function __construct(Point2DCollection $vertexes, IntegerMatrix $connections, ?Point2DGraph $parent = null)
    {
        $this->vertexes = $vertexes;
        $this->connections = $connections;
        $this->parent = $parent;
        $this->children = new Point2DGraphCollection();
    }

    public function getVertexes(): Point2DCollection
    {
        return $this->vertexes;
    }

    public function getConnections(): IntegerMatrix
    {
        return $this->connections;
    }

    public function getParent(): ?Point2DGraph
    {
        return $this->parent;
    }

    public function getChildren(): Point2DGraphCollection
    {
        return $this->children;
    }
}
