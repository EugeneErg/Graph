<?php declare(strict_types=1);
namespace EugeneErg\Graph\Processes\SvgAnimation\ValueObject;

use EugeneErg\Graph\ValueObjects\AbstractValueObject;

class ConnectionState extends AbstractValueObject
{
    private int $duration;
    private int $delay;
    private ?ConnectionState $parent;
    private ?string $color;

    public function __construct(
        int $duration = 0,
        int $delay = 0,
        ?ConnectionState $parent = null,
        ?string $color = null
    ) {
        $this->delay = $delay;
        $this->duration = $duration;
        $this->parent = $parent;
        $this->color = $color;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function getParent(): ?ConnectionState
    {
        return $this->parent;
    }

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function getDelay(): int
    {
        return $this->delay;
    }
}
