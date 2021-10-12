<?php declare(strict_types = 1);
namespace EugeneErg\Graph\Collections;

use ArrayAccess;
use Countable;
use EugeneErg\Graph\ValueObjects\AbstractValueObject;
use InvalidArgumentException;
use Iterator;
use JsonSerializable;

abstract class AbstractCollection extends AbstractValueObject implements JsonSerializable, Iterator, ArrayAccess, Countable
{
    /** @var array */
    private $records;

    public function __construct($records = [])
    {
        $this->validateRecords($records);
        $this->records = $records;
    }

    /**
     * @param array $records
     * @return $this
     */
    protected static function fromArray(array $records = []): self
    {
        return new static($records);
    }

    public function current()
    {
        return $this->valid() ? current($this->records) : null;
    }

    public function next()
    {
        $value = next($this->records);

        return $this->valid() ? $value : null;
    }

    /** @return int|string|null */
    public function key()
    {
        return key($this->records);
    }

    public function valid(): bool
    {
        return $this->key() !== null;
    }

    public function rewind()
    {
        $value = reset($this->records);

        return $this->valid() ? $value : null;
    }

    /** @param int|string $offset */
    public function offsetExists($offset): bool
    {
        return isset($this->records[$offset]);
    }

    /** @param int|string $offset */
    public function offsetGet($offset)
    {
        return $this->records[$offset];
    }

    /** @param int|string $offset */
    public function offsetSet($offset, $value): void
    {
        $this->validate($value, $offset);
        $this->records[$offset] = $value;
    }

    /** @param int|string $offset */
    public function offsetUnset($offset): void
    {
        unset($this->records[$offset]);
    }

    public function toArray(): array
    {
        return $this->records;
    }

    public function jsonSerialize(): array
    {
        return $this->records;
    }

    public function __debugInfo(): array
    {
        return $this->records;
    }

    private function validateRecords(array $records): void
    {
        array_walk($records, [$this, 'validate']);
    }

    private function validate($value, $key): void
    {
        if (!static::isValidElement($value) || ($key !== null && !static::isValidKey($key))) {
            throw new InvalidArgumentException();
        }
    }

    abstract public static function isValidElement($value): bool;

    public static function isValidKey($key): bool
    {
        return true;
    }

    public function count(): int
    {
        return count($this->records);
    }

    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }

    /**
     * @param AbstractCollection ...$replacements
     * @return $this
     */
    public static function replace(self ...$replacements): self
    {
        return static::fromArray(
            count($replacements) > 0
                ? array_replace(...self::collectionsToArrays(...$replacements))
                : []
        );
    }

    public static function keys(AbstractCollection $collection, $searchValue = null, bool $strict = false): self
    {
        if (func_num_args() === 1) {
            return static::fromArray(array_keys($collection->records));
        }

        $collection->validate($searchValue, null);

        return static::fromArray(array_keys($collection->records, $searchValue, $strict));
    }

    /**
     * @param AbstractCollection $collection
     * @param callable $callback
     * @return $this
     */
    public static function walk(AbstractCollection $collection, callable $callback): self
    {
        $array = $collection->toArray();
        array_walk($array, function (&$item, $key) use ($callback) {
            $item = $callback($item, $key);
        });

        return static::fromArray($array);
    }

    public function foreach(callable $callback, int $level = 1): void
    {
        $this->recursiveForeach($this, $callback, $level);
    }

    private static function collectionsToArrays(self ...$arrays): array
    {
        return array_map(static function (self $array): array {
            return $array->records;
        }, $arrays);
    }

    /**
     * @param callable $callback
     * @param AbstractCollection ...$collections
     * @return $this
     */
    public static function map(callable $callback, AbstractCollection ...$collections): self
    {
        return static::fromArray(
            count($collections) === 0
                ? []
                : array_map($callback, static::collectionsToArrays(...$collections))
        );
    }

    private function recursiveForeach($data, callable $callback, int $level, array $keys = []): void
    {
        if ($level === 0) {
            $callback($data, ...$keys);
        }

        foreach ($data as $key => $value) {
            $subKeys = $keys;
            array_unshift($subKeys, $key);
            $this->recursiveForeach($value, $callback, $level - 1, $subKeys);
        }
    }

    public static function __set_state(array $records): self
    {
        return static::fromArray($records);
    }
}
