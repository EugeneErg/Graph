<?php

declare(strict_types=1);

namespace EugeneErg\Graph\New\ValueObjects;

use EugeneErg\Collections\IntegerCollection;

class Canvas extends IntegerCollection
{
    public function __construct(public readonly Graph $graph)
    {
        parent::__construct(immutable: false);
    }

    public function offsetGet($offset): int
    {
        return $this->offsetExists($offset) ? parent::offsetGet($offset) : 0;
    }
}