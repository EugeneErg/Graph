<?php declare(strict_types = 1);
namespace EugeneErg\Graph\Collections;

class Collection extends AbstractLineCollection
{
    protected const ELEMENT_CLASS = Collection::class;

    public static function validateElement($value): void
    {
    }
}