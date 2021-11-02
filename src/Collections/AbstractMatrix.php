<?php declare(strict_types=1);
namespace EugeneErg\Graph\Collections;

abstract class AbstractMatrix extends AbstractCollection
{
    protected function createEmptyElement($key, array $items = []): AbstractCollection
    {
        $class = static::ELEMENT_CLASS;

        return is_a($class, self::class, true)
            ? $class::fromRecursiveArray($items)
            : $class::fromArray($items);
    }

    /** @return $this */
    public static function fromRecursiveArray(array $items = []): self
    {
        $result = static::fromArray();

        foreach ($items as $key => $item) {
            $result[$key] = $item instanceof AbstractCollection
            ? $item : $result->createEmptyElement($key, $item);
        }

        return $result;
    }
}
