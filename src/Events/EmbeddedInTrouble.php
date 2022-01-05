<?php declare(strict_types=1);
namespace EugeneErg\Graph\Events;

use EugeneErg\Graph\ValueObjects\Edge;
use EugeneErg\Graph\ValueObjects\Replacement;
use EugeneErg\Graph\ValueObjects\Trouble;

class EmbeddedInTrouble
{
    private Trouble $trouble;
    private Edge $edge;
    private Replacement $replacement;

    public function __construct(Trouble $trouble, Edge $edge, Replacement $replacement)
    {
        $this->trouble = $trouble;
        $this->edge = $edge;
        $this->replacement = $replacement;
    }

    public function getReplacement(): Replacement
    {
        return $this->replacement;
    }

    public function getEdge(): Edge
    {
        return $this->edge;
    }

    public function getTrouble(): Trouble
    {
        return $this->trouble;
    }
}
