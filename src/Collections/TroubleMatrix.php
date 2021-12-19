<?php declare(strict_types=1);
namespace EugeneErg\Graph\Collections;

use EugeneErg\Graph\ValueObjects\Trouble;

/**
 * @method Trouble getItem($collectionKey, $itemKey, bool $nullIfNotExists = false)
 */
class TroubleMatrix extends AbstractMatrix2
{
    protected const ELEMENT_CLASS = TroubleCollection::class;
}
