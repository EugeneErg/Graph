<?php declare(strict_types = 1);
namespace EugeneErg\Graph\ValueObjects\Temp;

class Replace extends AbstractTempDto
{
    /** @var int[] */
    public $old;
    /** @var int[] */
    public $new;

    public function __construct(array $old, array $new)
    {
        $this->old = $old;
        $this->new = $new;
    }
}
