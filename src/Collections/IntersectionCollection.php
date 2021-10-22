<?php declare(strict_types=1);
namespace EugeneErg\Graph\Collections;

use EugeneErg\Graph\Services\AssertService;
use EugeneErg\Graph\ValueObjects\Intersection;

class IntersectionCollection extends AbstractCollection
{
    public static function isValidElement($value): bool
    {
        AssertService::instance()->type(Intersection::class, $value);

        return true;
    }
}
