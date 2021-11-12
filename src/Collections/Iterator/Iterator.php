<?php declare(strict_types=1);

namespace EugeneErg\Graph\Collections\Iterator;

class Iterator implements \SeekableIterator, \ArrayAccess
{
    private $items;
    private $position;

    public function __construct(array ...$items)
    {
        $this->position = 0;
        $this->items = $this->prepareItems($items);
    }

    public function current()
    {
        return $this->items[$this->position] ?? null;
    }

    public function next()
    {
        $this->position++;

        return $this->current();
    }

    public function key(): ?string
    {
        return $this->items[$this->position]->key ?? null;
    }

    public function valid(): bool
    {
        return isset($this->items[$this->position]);
    }

    public function rewind()
    {
        $this->position = 0;

        return $this->current();
    }

    /** @param int $offset */
    public function offsetExists($offset): bool
    {
        return isset($this->items[$offset]);
    }

    /** @param int $offset */
    public function offsetGet($offset)
    {
        return $this->items[$offset];
    }

    /**
     * @param int $offset
     * @param array $value
     */
    public function offsetSet($offset, $value): void
    {
        $this->items[$offset] = $value;
    }

    /** @param int $offset */
    public function offsetUnset($offset): void
    {
        unset($this->items[$offset]);
    }

    /** @param int $offset */
    public function seek($offset)
    {
        $this->position = $offset;

        return $this->current();
    }

    public function unshift(array ...$items): int
    {
        return array_unshift($this->items, ...$this->prepareItems($items));
    }

    /** @return IteratorItem[] */
    private function prepareItems(array $items): array
    {
        $result = [];

        foreach ($items as $item) {
            foreach ($item as $key => $value) {
                $result[] = new IteratorItem((string) $key, $value);
            }
        }

        return $result;
    }

    /**
     * @param bool[] ...$isValues
     * @return array
     */
    public function list(array ...$isValues): array
    {
        $result = [];

        foreach ($isValues as $isValueByOffsets) {
            foreach ($isValueByOffsets as $offset => $isValue) {
                if ($isValue !== null) {
                    $result[] = $this->items[$offset][$isValue];
                }
            }
        }

        return $result;
    }

    public function keys(int ...$offsets): array
    {
        return array_column($this->items(...$offsets), 'key');
    }

    public function allKeys(): array
    {
        return array_column($this->items, 'key');
    }

    public function values(int ...$offsets): array
    {
        return array_column($this->items(...$offsets), 'value');
    }

    private function items(int ...$offsets): array
    {
        return array_map(function (int $offset): IteratorItem {
            return $this->items[$offset];
        }, $offsets);
    }
}
