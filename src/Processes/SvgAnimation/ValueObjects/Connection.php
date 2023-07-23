<?php declare(strict_types = 1);
namespace EugeneErg\Graph\Processes\SvgAnimation\ValueObjects;

use EugeneErg\Graph\ValueObjects\AbstractValueObjects;

class Connection extends AbstractValueObject
{
    private Vertex $from;
    private Vertex $to;
    private ?ConnectionState $state;

    public function __construct(Vertex $from, Vertex $to, ?ConnectionState $state = null)
    {
        $this->from = $from;
        $this->to = $to;
        $this->state = $state;
    }

    public function getColor(): ?ConnectionState
    {
        return $this->state;
    }

    public function getFrom(): Vertex
    {
        return $this->from;
    }

    public function getTo(): Vertex
    {
        return $this->to;
    }
}
