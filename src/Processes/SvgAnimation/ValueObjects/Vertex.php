<?php declare(strict_types = 1);
namespace EugeneErg\Graph\Processes\SvgAnimation\ValueObjects;

use EugeneErg\Graph\Dto\Point2D;
use EugeneErg\Graph\Processes\Collections\OptionCollection;
use EugeneErg\Graph\ValueObjects\AbstractValueObjects;
use EugeneErg\Graph\ValueObjects\Options\Point2DOption;
use EugeneErg\Graph\ValueObjects\Options\ColorOption;

class Vertex extends AbstractValueObject
{
    private int $id;
    private State $state;

    public function __construct(int $id, State $state)
    {
        $this->id = $id;
        $this->state = $state;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getState(): State
    {
        return $this->state;
    }

    public function brush(string $color, int $duration, int $delay = 0): void
    {
        $this->state = new State(
            new OptionCollection([new ColorOption($color)]),
            $duration,
            $delay,
            $this->state
        );
    }

    public function move(Point2D $coordinate, int $duration, int $delay = 0): void
    {
        $this->state = new State(
            new OptionCollection([new Point2DOption($coordinate)]),
            $duration,
            $delay,
            $this->state
        );
    }

    public function __clone()
    {
        $this->state = clone $this->state;
    }
}
