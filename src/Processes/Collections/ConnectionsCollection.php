<?php declare(strict_types = 1);
namespace EugeneErg\Graph\Processes\Collections;

use EugeneErg\Graph\Collections\AbstractLineCollection;
use EugeneErg\Graph\Processes\SvgAnimation\ValueObject\Connection;

class ConnectionsCollection extends AbstractLineCollection
{
    protected const ELEMENT_CLASS = Connection::class;
}
