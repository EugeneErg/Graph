<?php declare(strict_types=1);
namespace EugeneErg\Graphs;

use EugeneErg\Graph\Collections\AbstractMatrix2;

class TroubleTreeMatrix extends AbstractMatrix2
{
    protected const ELEMENT_CLASS = TroubleTreeCollection::class;
}
