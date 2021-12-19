<?php declare(strict_types=1);
namespace EugeneErg\Graph\Collections;

use EugeneErg\Graph\ValueObjects\Trouble;

/**
 * @method Trouble offsetGet($offset)
 */
class TroubleCollection extends AbstractLineCollection
{
    protected const ELEMENT_CLASS = Trouble::class;
}
