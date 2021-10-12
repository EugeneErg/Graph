<?php declare(strict_types = 1);
namespace EugeneErg\Graph\Collections;

class Collection extends AbstractCollection
{
    public static function isValidElement($value): bool
    {
        return true;
    }
}