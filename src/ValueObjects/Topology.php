<?php namespace EugeneErg\Graphs\ValueObjects;

/**
 * @property-read Edge $outer
 * @property-read Arc[] $arcs
 */
class Topology extends AbstractValueObjectMutable
{
    public function __construct(Edge $outer, array $arcs)
    {
        parent::__construct($outer, $arcs);
    }
}