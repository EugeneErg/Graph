<?php declare(strict_types=1);

namespace EugeneErg\Graph\ValueObjects\Options;

class ColorOption implements OptionInterface
{
    private string $color;

    public function __construct(string $color)
    {
        $this->color = $color;
    }

    public function getValue(): string
    {
        return $this->color;
    }
}
