<?php declare(strict_types=1);
namespace EugeneErg\Graph\Collections;

use EugeneErg\Graph\ValueObjects\Solution;
use Generator;

/**
 * @method Generator|Solution[] getIterator()
 */
class SolutionCollection extends AbstractLineCollection
{
    protected const ELEMENT_CLASS = Solution::class;
}
