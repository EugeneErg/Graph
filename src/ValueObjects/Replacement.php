<?php declare(strict_types = 1);
namespace EugeneErg\Graph\ValueObjects;

use EugeneErg\Graph\Collections\IntegerCollection;

/**
 * @see Replacement::getVertexes()
 * @property-read IntegerCollection $vertexes
 * @see Replacement::getStart()
 * @property-read int $start
 * @see Replacement::getLength()
 * @property-read int $length
 * @see Replacement::getFirstVertex()
 * @property-read int $firstVertex
 * @see Replacement::getLastVertex()
 * @property-read int $lastVertex
 */
class Replacement extends AbstractValueObject
{
    private $vertexes;
    private $start;
    private $length;

    public function __construct(IntegerCollection $vertexes, int $start, int $length)
    {
        $this->vertexes = $vertexes;
        $this->start = $start;
        $this->length = $length;
    }

    protected function getFirstVertex(): int
    {
        return $this->vertexes->getValueByPosition(0);
    }

    protected function getLastVertex(): int
    {
        return $this->vertexes->getValueByPosition();
    }

    protected function getVertexes(): IntegerCollection
    {
        return $this->vertexes;
    }

    protected function getLength(): int
    {
        return $this->length;
    }

    protected function getStart(): int
    {
        return $this->start;
    }
}