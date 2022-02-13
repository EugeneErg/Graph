<?php declare(strict_types=1);
namespace EugeneErg\Graph\Collections\Sort;

use EugeneErg\Graph\Enums\AbstractEnum;

/**
 * @method static self REGULAR()
 * @method static self NUMERIC()
 * @method static self STRING()
 * @method static self LOCALE_STRING()
 * @method static self NATURAL()
 * @method static self FLAG_CASE()
*/
final class SortFlagEnum extends AbstractEnum
{
    protected static array $values = [
        'REGULAR' => SORT_REGULAR,
        'NUMERIC' => SORT_NUMERIC,
        'STRING' => SORT_STRING,
        'LOCALE_STRING' => SORT_LOCALE_STRING,
        'NATURAL' => SORT_NATURAL,
        'FLAG_CASE' => SORT_FLAG_CASE,
    ];

    public function __invoke(string $value1, string $value2): int
    {
        if ($value1 === $value2) {
            return 0;
        }

        $array = [$value1, $value2];
        sort($array, $this->getValue());

        return $array[0] === $value1 ? -1 : 1;
    }
}
