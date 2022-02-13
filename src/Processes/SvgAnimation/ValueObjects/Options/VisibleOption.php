<?php declare(strict_types=1);

namespace EugeneErg\Graph\ValueObjects\Options;

class VisibleOption implements OptionInterface
{
    private bool $visible;

    public function __construct(bool $visible)
    {
        $this->visible = $visible;
    }

    public function getValue(): bool
    {
        return $this->visible;
    }
}
