<?php declare(strict_types=1);
namespace EugeneErg\Graph\Collections;

use LogicException;

class Interruption extends LogicException
{
    public function __construct(string $type, int $level = 1, ?Interruption $previous = null)
    {
        parent::__construct($type, $level, $previous);
    }

    public function next(): self
    {
        return new Interruption($this->getMessage(), $this->getCode() - 1, $this);
    }
}