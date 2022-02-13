<?php declare(strict_types=1);

namespace EugeneErg\Graph\Processes\Collections;

use EugeneErg\Graph\Collections\AbstractLineCollection;
use EugeneErg\Graph\Collections\StringCollection;
use EugeneErg\Graph\Processes\SvgAnimation\ValueObject\State;

/**
 * @method State[] getIterator()
 */
class StateCollection extends AbstractLineCollection
{
    protected const ELEMENT_CLASS = State::class;


    public function groupByOptions(): GroupStateCollection
    {
        $result = new GroupStateCollection();
        $currentOptions = new StringCollection();

        foreach ($this as $state) {
            $classes = $state->getOptions()->getClasses();

            if ($state->getDelay() !== 0
                || $currentOptions->count() !== $state->getOptions()->count()
                || $classes->intersect($currentOptions)->count() !== $currentOptions->count()
            ) {
                $currentOptions = $classes;
                $result[] = $currentOptions;
            }

            $currentOptions[] = $state;
        }
    }


}
