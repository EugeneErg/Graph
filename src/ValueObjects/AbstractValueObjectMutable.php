<?php declare(strict_types = 1);
namespace EugeneErg\Graph\ValueObjects;

use Exception;
use JsonSerializable;

abstract class AbstractValueObjectMutable implements JsonSerializable
{
    use ValueObjectTrait {
        getAttributes as protected;
    }

    /**
     * @param string $name
     * @param $value
     * @throws Exception
     */
    public function __set(string $name, $value): void
    {
        $this->__call('set' . ucfirst($name), [$value]);
    }
}
