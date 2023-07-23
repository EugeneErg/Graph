<?php

declare(strict_types=1);

namespace EugeneErg\Graph\New\Animations\Collections;

use EugeneErg\Collections\ObjectCollection as AbstractCollection;
use EugeneErg\Graph\New\Animations\Segments\SegmentInterface;

/**
 * @method SegmentInterface[] getIterator()
 * @method SegmentInterface last()
 * @method SegmentInterface offsetGet(mixed $offset)
 */
class AbstractSegmentCollection extends AbstractCollection
{
    protected const VALUE_TYPE = SegmentInterface::class;
}