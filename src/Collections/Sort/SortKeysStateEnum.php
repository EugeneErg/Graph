<?php declare(strict_types=1);

namespace EugeneErg\Graph\Collections\Sort;

use EugeneErg\Graph\Enums\AbstractEnum;

/**
 * @method static self BY_KEYS()
 * @method static self WITH_KEYS()
 * @method static self WITHOUT_KEYS()
 */
class SortKeysStateEnum extends AbstractEnum
{
    protected static $values = [
        'BY_KEYS' => 'by_keys',
        'WITH_KEYS' => 'with_keys',
        'WITHOUT_KEYS' => 'without_keys',
    ];
}
