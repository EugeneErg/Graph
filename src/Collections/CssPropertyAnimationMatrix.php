<?php declare(strict_types=1);
namespace EugeneErg\Graph\Collections;

/**
 * @method CssPropertyAnimationCollection[] getIterator()
 * @method CssPropertyAnimationCollection getCollection($collectionKey, bool $nullIfNotExists = false)
 */
class CssPropertyAnimationMatrix extends AbstractMatrix2
{
    protected const ELEMENT_CLASS = CssPropertyAnimationCollection::class;
}
