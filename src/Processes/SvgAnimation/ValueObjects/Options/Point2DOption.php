<?php declare(strict_types=1);

namespace EugeneErg\Graph\ValueObjects\Options;

use EugeneErg\Graph\Dto\Point2D;

class Point2DOption implements OptionInterface
{
    private Point2D $point;

    public function __construct(Point2D $point)
    {
        $this->point = $point;
    }

    public function getValue(): Point2D
    {
        return $this->point;
    }
}
