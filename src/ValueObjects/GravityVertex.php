<?php namespace EugeneErg\Graph\ValueObjects;

/**
 * @property-read int[] vertexes
 */
class GravityVertex extends AbstractValueObjectMutable implements GravityInterface
{
    public function __construct(int ...$vertexes)
    {
        parent::__construct($vertexes);
    }

    public function toArray(): array
    {
        return $this->vertexes;
    }
}
