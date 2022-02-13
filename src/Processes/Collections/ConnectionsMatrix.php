<?php declare(strict_types = 1);
namespace EugeneErg\Graph\Processes\Collections;

use EugeneErg\Graph\Collections\AbstractMatrix2;

class ConnectionsMatrix extends AbstractMatrix2
{
    protected const ELEMENT_CLASS = ConnectionsCollection::class;
}
