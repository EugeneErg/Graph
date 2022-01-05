<?php declare(strict_types=1);

namespace EugeneErg\Graph\Collections;

use EugeneErg\Graph\Dto\CSS\CssPropertyAnimationDto;

/**
 * @method CssPropertyAnimationDto getIterator()
 */
class CssPropertyAnimationCollection extends AbstractLineCollection
{
    protected const ELEMENT_CLASS = CssPropertyAnimationDto::class;
}
