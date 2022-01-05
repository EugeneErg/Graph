<?php declare(strict_types=1);

namespace EugeneErg\Graph\Collections;

use EugeneErg\Graph\Dto\Point2D;
use EugeneErg\Graph\Services\Assert\Argument;
use EugeneErg\Graph\Services\AssertService;

class CssRuleKeyFramesPoint2DCollection extends AbstractLineCollection
{
    protected const ELEMENT_CLASS = Point2D::class;

    public static function validateKey($key): void
    {
        AssertService::instance()->matchesPattern(function ($value): bool {
            return in_array($value, ['from', 'to'], true) || is_numeric($value);
        }, new Argument($key, 1, 'key'));
    }
}
