<?php declare(strict_types=1);

namespace EugeneErg\Graph\Processes\Collections;

use EugeneErg\Graph\Collections\AbstractLineCollection;
use EugeneErg\Graph\Collections\StringCollection;
use EugeneErg\Graph\ValueObjects\Options\OptionInterface;

class OptionCollection extends AbstractLineCollection
{
    protected const ELEMENT_CLASS = OptionInterface::class;

    public function getClasses(): StringCollection
    {
        return StringCollection::fromMap(function (OptionInterface $option): string {
            return get_class($option);
        });
    }
}
