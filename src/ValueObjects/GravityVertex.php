<?php declare(strict_types = 1);
namespace EugeneErg\Graph\ValueObjects;

use EugeneErg\Graph\Collections\IntegerCollection;

class GravityVertex extends IntegerCollection implements GravityInterface
{
    public function toArray(): array
    {
        return parent::toArray();
    }
}
