<?php declare(strict_types=1);

namespace EugeneErg\Graph\Collections\Sort;

use EugeneErg\Graph\Collections\AbstractCollection;

final class Sort
{
    private $collection;
    private $flag;
    private $direction;

    public function __construct(
        AbstractCollection $collection,
        ?SortDirectionEnum $direction = null,
        ?SortFlagEnum $flag = null
    ) {
        $this->collection = $collection;
        $this->flag = $flag ?? SortFlagEnum::REGULAR();
        $this->direction = $direction ?? SortDirectionEnum::ASC();
    }

    public function getCollection(): AbstractCollection
    {
        return $this->collection;
    }

    public function getFlag(): SortFlagEnum
    {
        return $this->flag;
    }

    public function getDirection(): SortDirectionEnum
    {
        return $this->direction;
    }
}
