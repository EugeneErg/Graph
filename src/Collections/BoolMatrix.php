<?php declare(strict_types=1);

namespace EugeneErg\Graph\Collections;

/**
 * @method BoolCollection getCollection($collectionKey, bool $nullIfNotExists = false)
 */
class BoolMatrix extends AbstractMatrix2
{
    protected const ELEMENT_CLASS = BoolCollection::class;
}
