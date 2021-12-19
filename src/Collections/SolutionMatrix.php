<?php declare(strict_types=1);
namespace EugeneErg\Graph\Collections;

use EugeneErg\Graph\ValueObjects\Solution;

/**
 * @method Solution getItem($collectionKey, $itemKey, bool $nullIfNotExists = false)
 * @method SolutionCollection getCollection($collectionKey, bool $nullIfNotExists = false)
 */
class SolutionMatrix extends AbstractMatrix2
{
    protected const ELEMENT_CLASS = SolutionCollection::class;
}
