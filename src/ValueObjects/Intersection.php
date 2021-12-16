<?php declare(strict_types = 1);
namespace EugeneErg\Graph\ValueObjects;

use EugeneErg\Graph\Collections\BoolCollection;
use EugeneErg\Graph\Collections\IntegerCollection;

/**
 * @see Intersection::getVertexes()
 * @property-read IntegerCollection $vertexes
 * @see Intersection::getConnections()
 * @property-read BoolCollection $connections
 * @see Intersection::setIsOuter()
 * @see Intersection::getIsOuter()
 * @property bool|null $isOuter
 */
class Intersection extends AbstractValueObject
{
    private $vertexes;
    private $connections;
    private $isOuter;

    public function __construct(IntegerCollection $vertexes, BoolCollection $connections, ?bool $isOuter = null)
    {
        $this->vertexes = $vertexes;
        $this->connections = $connections;
        $this->isOuter = $isOuter;
    }

    public function getConnections(): BoolCollection
    {
        return $this->connections;
    }

    public function getIsOuter(): ?bool
    {
        return $this->isOuter;
    }

    public function getVertexes(): IntegerCollection
    {
        return $this->vertexes;
    }

    public function setIsOuter(bool $isOuter): void
    {
        $this->isOuter = $isOuter;
    }

    public function toArray(): array
    {
        return [
            'vertexes' => $this->vertexes,
            'connections' => $this->connections,
            'isOuter' => $this->isOuter,
        ];
    }
}
