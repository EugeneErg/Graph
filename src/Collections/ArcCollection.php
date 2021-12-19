<?php declare(strict_types=1);

namespace EugeneErg\Graph\Collections;

use EugeneErg\Graph\ValueObjects\Arc;

class ArcCollection extends AbstractLineCollection
{
    protected const ELEMENT_CLASS = Arc::class;
}
