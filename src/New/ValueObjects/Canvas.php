<?php

declare(strict_types=1);

namespace EugeneErg\Graph\New\ValueObjects;

class Canvas
{
    public function __construct(public readonly Graph $graph)
    {
    }
}