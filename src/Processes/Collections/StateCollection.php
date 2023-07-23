<?php declare(strict_types=1);

namespace EugeneErg\Graph\Processes\Collections;

use EugeneErg\Graph\Collections\AbstractLineCollection;
use EugeneErg\Graph\Collections\StringCollection;
use EugeneErg\Graph\Processes\SvgAnimation\ValueObjects\GroupState;
use EugeneErg\Graph\Processes\SvgAnimation\ValueObjects\State;

/**
 * @method State[] getIterator()
 */
class StateCollection extends AbstractLineCollection
{
    protected const ELEMENT_CLASS = State::class;

    public function groupByOptions(): GroupStateCollection
    {
        $result = new GroupStateCollection();
        $delay = 0;
        $currentOptions = new StringCollection();
        $currentOptionsCount = 0;

        foreach ($this as $state) {
            $classes = $state->getOptions()->getClasses();

            if ($state->getDelay() !== 0
                || $state->getOptions()->count() !== $currentOptionsCount
                || $classes->intersect($currentOptions)->count() !== $currentOptionsCount
            ) {
                $currentStateCollection = new StateCollection();
                $currentOptions = $classes;
                $result[] = new GroupState($delay, $currentStateCollection);
                $currentOptionsCount = $state->getOptions()->count();
            }

            $delay += $state->getDelay() + $state->getDuration();
            $currentStateCollection[] = $state;
        }

        return $result;
    }
}
