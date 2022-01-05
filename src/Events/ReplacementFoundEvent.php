<?php declare(strict_types=1);

namespace EugeneErg\Graph\Events;

use EugeneErg\Graph\ValueObjects\Replacement;

class ReplacementFoundEvent
{
    private Replacement $replacement;

    public function __construct(Replacement $replacement)
    {
        $this->replacement = $replacement;
    }

    public function getReplacement(): Replacement
    {
        return $this->replacement;
    }
}
