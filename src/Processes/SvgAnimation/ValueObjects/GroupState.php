<?php declare(strict_types=1);

namespace EugeneErg\Graph\Processes\SvgAnimation\ValueObject;

use EugeneErg\Graph\Processes\Collections\StateCollection;

class GroupState
{
    private int $delay;
    private StateCollection $states;

    public function __construct(int $delay, StateCollection $stateCollection)
    {
        $this->delay = $delay;
        $this->states = $stateCollection;
    }

    public function getDelay(): int
    {
        return $this->delay;
    }

    public function getStates(): StateCollection
    {
        return $this->states;
    }

    public function getDuration(): int
    {
        return $this->states->reduce(function (int $duration, State $state): int {
            return $duration + $state->getDuration();
        }, 0);
    }
}
