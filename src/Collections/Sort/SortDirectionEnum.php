<?php declare(strict_types=1);

namespace EugeneErg\Graph\Collections\Sort;

use EugeneErg\Graph\Enums\AbstractEnum;

/**
 * @method static self ASC()
 * @method static self DESC()
 */
class SortDirectionEnum extends AbstractEnum
{
    protected static $values = [
        'ASC' => SORT_ASC,
        'DESC' => SORT_DESC,
    ];
}
