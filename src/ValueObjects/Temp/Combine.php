<?php namespace EugeneErg\Graphs\ValueObjects\Temp;

class Combine extends AbstractTempDto
{
    /** @var int */
    public $pos;
    /** @var int[] */
    public $oldVertexes;
    /** @var int[] */
    public $newVertexes;

    public function __construct(int $pos, array $old, array $new)
    {
        $this->pos = $pos;
        $this->oldVertexes = $old;
        $this->newVertexes = $new;
    }
}