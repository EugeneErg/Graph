<?php declare(strict_types=1);

namespace EugeneErg\Graph\Processes\SvgAnimation\ValueObject;

use EugeneErg\Graph\Processes\Collections\StateCollection;

class GroupState
{
    private int $delay;
    private StateCollection $states;

    public function __construct(int $delay, StateCollection $states)
    {
        $this->delay = $delay;
        $this->states = $states;
    }
}
