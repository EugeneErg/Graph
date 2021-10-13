<?php declare(strict_types = 1);
namespace EugeneErg\Graph\ValueObjects;

/**
 * @property-read GravityInterface[] $gravities
 */
class Gravity extends AbstractValueObjectMutable implements GravityInterface
{
    public function __construct(GravityInterface ...$gravities)
    {
        parent::__construct($gravities);
    }

    public function toArray(): array
    {
        return $this->gravities;
    }
}