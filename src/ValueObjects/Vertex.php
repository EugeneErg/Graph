<?php declare(strict_types = 1);
namespace EugeneErg\Graph\ValueObjects;

/**
 * @see Vertex::getName()
 * @property-read string $name
 */
class Vertex extends AbstractValueObject
{
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
