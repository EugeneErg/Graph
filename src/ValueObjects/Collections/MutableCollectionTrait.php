<?php namespace EugeneErg\Graphs\ValueObjects\Collections;

trait MutableCollectionTrait
{
    public function splice(
        int $offset,
        ?int $length = null,
        ?self $replacement = null
    ): self {
        return static::create(array_splice($this->records, $offset, $length, $replacement->records));
    }

    public function walk(callable $callback): bool
    {
        return array_walk($this->records, $callback);
    }

    public function push(...$values): int
    {
        return array_push($this->records, ...$values);
    }

    public function shift()
    {
        return array_shift( $this->records);
    }

    public function pop()
    {
        return array_pop($this->records);
    }

    public function unshift(...$values): int
    {
        return array_unshift($this->records, ...$values);
    }

    public function offsetSet($offset, $value): void
    {
        $this->records[$offset] = $value;
    }

    public function offsetUnset($offset): void
    {
        unset($this->records[$offset]);
    }

    public function shuffle(): void
    {
        shuffle($this->records);
    }

    /**
     * @param callable|array|null $sortMethod
     * @param int $sortOrder
     * @param bool $preserveKeys
     */
    public function sort($sortMethod = null, int $sortOrder = SORT_ASC, bool $preserveKeys = false): void
    {
        if ($sortMethod ?? static::SORT_TYPE === null) {
            $sortMethod = $sortOrder === SORT_ASC
                /** @see AbstractCollection::compareValue() */
                ? [$this, 'isEqual']
                : static function ($elementA, $elementB): int {
                    return $this->isEqual($elementB, $elementA);
                };
        } elseif (is_callable($sortMethod) && $sortOrder !== SORT_ASC) {
            $sortMethod = static function ($elementA, $elementB) use($sortMethod): int {
                return $sortMethod($elementB, $elementA);
            };
        } elseif (!is_callable($sortMethod) && is_array($sortMethod) && $preserveKeys) {
            $keys = array_keys($this->records);
            array_multisort($keys, $sortMethod);

            if ($sortOrder !== SORT_ASC) {
                $keys = array_reverse($keys);
            }

            $this->records = array_intersect_key(array_flip($keys), $this->records);

            return;
        }

        if (is_callable($sortMethod)) {
            $preserveKeys
                ? uasort($this->records, $sortMethod)
                : usort($this->records, $sortMethod);

            return;
        }

        if (is_array($sortMethod)) {
            array_multisort($this->records, $sortMethod);

            if ($sortOrder !== SORT_ASC) {
                $this->records = array_reverse($this->records);
            }

            return;
        }

        $preserveKeys ? (
        $sortOrder === SORT_ASC
            ? asort($this->records, static::SORT_TYPE)
            : arsort($this->records, static::SORT_TYPE)
        ) : (
        $sortOrder === SORT_ASC
            ? sort($this->records, static::SORT_TYPE)
            : rsort($this->records, static::SORT_TYPE)
        );
    }

    /**
     * @param callable|int|null $sortMethod
     * @param int $sortOrder
     */
    public function sortKeys($sortMethod = null, int $sortOrder = SORT_ASC): void
    {
        if (is_callable($sortMethod) && $sortOrder !== SORT_ASC) {
            $sortMethod = static function ($key1, $key2) use ($sortMethod): int {
                return $sortMethod($key2, $key1);
            };
        } elseif ($sortMethod === null) {
            $sortMethod = $sortOrder === SORT_ASC
                /** @see AbstractCollection::isEqualKeys() */
                ? [$this, 'isEqualKeys']
                : static function ($elementA, $elementB): int {
                    return $this->isEqualKeys($elementB, $elementA);
                };
        }

        is_callable($sortMethod)
            ? uksort($this->records, $sortMethod)
            : ($sortOrder === SORT_ASC
            ? ksort($this->records, $sortMethod)
            : krsort($this->records, $sortMethod)
        );
    }
}