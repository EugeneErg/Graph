<?php declare(strict_types=1);
namespace EugeneErg\Graph\Collections;

use EugeneErg\Graph\Collections\AbstractMatrix2;
use EugeneErg\Graph\Collections\TroubleTreeCollection;

class TroubleTreeMatrix extends AbstractMatrix2
{
    protected const ELEMENT_CLASS = TroubleTreeCollection::class;
}
