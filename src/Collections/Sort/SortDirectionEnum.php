<?php declare(strict_types=1);

namespace EugeneErg\Graph\Collections\Sort;

use EugeneErg\Graph\Enums\AbstractBackedEnum;

/**
 * @method static self ASC()
 * @method static self DESC()
 */
final class SortDirectionEnum extends AbstractBackedEnum
{
    protected static array $cases = [
        'ASC' => SORT_ASC,
        'DESC' => SORT_DESC,
    ];
}
