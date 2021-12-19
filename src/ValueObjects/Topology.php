<?php declare(strict_types = 1);
namespace EugeneErg\Graph\ValueObjects;

use EugeneErg\Graph\Collections\ArcCollection;

/**
 * @see Topology::getOuter()
 * @property-read Edge $outer
 * @see Topology::getArcs()
 * @property-read Arc[] $arcs
 */
class Topology extends AbstractValueObject
{
    private $outer;
    private $arcs;

    public function __construct(Edge $outer, ArcCollection $arcs)
    {
        $this->outer = $outer;
        $this->arcs = $arcs;
    }

    public function getArcs(): ArcCollection
    {
        return $this->arcs;
    }

    public function getOuter(): Edge
    {
        return $this->outer;
    }
}