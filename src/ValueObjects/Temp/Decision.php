<?php namespace EugeneErg\Graphs\ValueObjects\Temp;

class Decision extends AbstractTempDto
{
    public const TYPE_ABSORPTION = 'absorption';
    public const TYPE_EMBEDDING = 'embedding';

    public $fromPosition;
    public $toPosition;
    public $type;
    /** @var Problem */
    public $problem;

    public function __construct(?int $from, ?int $to, string $type)
    {
        $this->fromPosition = $from;
        $this->toPosition = $to;
        $this->type = $type;
    }
}
