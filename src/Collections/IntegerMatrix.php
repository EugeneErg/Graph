<?php declare(strict_types = 1);
namespace EugeneErg\Graph\Collections;

use EugeneErg\Graph\Services\AssertService;

/**
 * @method __construct(IntegerCollection[] $items)
 * @method IntegerCollection[] getIterator()
 * @method IntegerCollection[] toArray()
 * @method IntegerCollection getCollection($collectionKey, bool $nullIfNotExists = false)
 */
class IntegerMatrix extends AbstractMatrix2
{
    protected const ELEMENT_CLASS = IntegerCollection::class;

    public static function validateKey($key): void
    {
        AssertService::instance()->type(['integer', 'NULL'], $key);
    }
}
