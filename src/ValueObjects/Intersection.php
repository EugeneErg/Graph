<?php namespace EugeneErg\Graph\ValueObjects;

/**
 * @property-read int[] $vertexes
 * @property-read int[] $connections
 * @see Intersection::setIsOuterAttribute()
 * @property bool|null $isOuter
 */
class Intersection extends AbstractValueObjectMutable
{
    /**
     * @param int[] $vertexes
     * @param int[] $connections
     * @param bool|null $isOuter
     */
    public function __construct(array $vertexes, array $connections, ?bool $isOuter = null)
    {
        parent::__construct($vertexes, $connections, $isOuter);
    }

    protected function setIsOuterAttribute(object $attributes, bool $value): void
    {
        $attributes->isOuter = $value;
    }
}
