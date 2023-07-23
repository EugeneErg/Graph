<?php declare(strict_types = 1);
namespace EugeneErg\Graph\Processes\Collections;

use EugeneErg\Graph\Collections\AbstractLineCollection;
use EugeneErg\Graph\Processes\SvgAnimation\ValueObjects\Graph;

/**
 * @method Graph[] getIterator()
 */
class GraphCollection extends AbstractLineCollection
{
    protected const ELEMENT_CLASS = Graph::class;
}
