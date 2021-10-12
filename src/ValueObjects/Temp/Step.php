<?php namespace EugeneErg\Graphs\ValueObjects\Temp;

class Step extends AbstractTempDto
{
    public $begin;
    public $end;
    public $arc;
    public $center;

    /**
     * Step constructor.
     * @param int $begin
     * @param int $end
     * @param int[] $arc
     * @param int|array|null $center
     */
    public function __construct(int $begin, int $end, array $arc, $center)
    {
        $this->begin = $begin;
        $this->end = $end;
        $this->arc = $arc;
        $this->center = $center;
    }
}