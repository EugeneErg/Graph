<?php declare(strict_types=1);
namespace EugeneErg\Graph\Enums;

/**
 * @method static self KEY()
 * @method static self BOTH()
 * @method static self VALUE()
 */
final class CollectionFilterEnum extends AbstractBackedEnum
{
    protected static array $cases = [
        'KEY' => ARRAY_FILTER_USE_KEY,
        'BOTH' => ARRAY_FILTER_USE_BOTH,
        'VALUE' => 0,
    ];
}
