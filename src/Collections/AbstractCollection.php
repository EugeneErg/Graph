<?php declare(strict_types = 1);
namespace EugeneErg\Graph\Collections;

use ArrayAccess;
use Countable;
use EugeneErg\Graph\Services\Assert\Argument;
use EugeneErg\Graph\Services\AssertService;
use EugeneErg\Graph\ValueObjects\AbstractValueObject;
use Iterator;
use JsonSerializable;
use TypeError;

abstract class AbstractCollection extends AbstractValueObject implements JsonSerializable, Iterator, ArrayAccess, Countable
{
    /** @var array */
    private $items;

    public function __construct(array $items = [])
    {
        $this->validateItems($items);
        $this->items = $items;
    }

    /**
     * @param array $items
     * @return $this
     */
    protected static function fromArray(array $items = []): self
    {
        return new static($items);
    }

    public function current()
    {
        return $this->valid() ? current($this->items) : null;
    }

    public function next()
    {
        $value = next($this->items);

        return $this->valid() ? $value : null;
    }

    /** @return int|string|null */
    public function key()
    {
        return key($this->items);
    }

    public function valid(): bool
    {
        return $this->key() !== null;
    }

    public function rewind()
    {
        $value = reset($this->items);

        return $this->valid() ? $value : null;
    }

    /** @param int|string $offset */
    public function offsetExists($offset): bool
    {
        return isset($this->items[$offset]);
    }

    /** @param int|string $offset */
    public function offsetGet($offset)
    {
        return $this->items[$offset];
    }

    /** @param int|string|null $offset */
    public function offsetSet($offset, $value): void
    {
        $this->validate($value, $offset);
        $offset === null ? $this->items[] = $value : $this->items[$offset] = $value;
    }

    /** @param int|string $offset */
    public function offsetUnset($offset): void
    {
        unset($this->items[$offset]);
    }

    public function toArray(): array
    {
        return $this->items;
    }

    public function jsonSerialize(): array
    {
        return $this->items;
    }

    public function __debugInfo(): array
    {
        return $this->items;
    }

    private function validateItems(array $items): void
    {
        array_walk($items, [$this, 'validate']);
    }

    private function validate($value, $key): void
    {
        if (!static::isValidElement($value) || ($key !== null && !static::isValidKey($key))) {
            throw new TypeError();
        }
    }

    abstract public static function isValidElement($value): bool;

    public static function isValidKey($key): bool
    {
        return true;
    }

    public function count(): int
    {
        return count($this->items);
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

    /**
     * @param AbstractCollection ...$replacements
     * @return $this
     */
    public static function merge(self ...$replacements): self
    {
        return static::fromArray(
            count($replacements) > 0
                ? array_merge(...self::collectionsToArrays(...$replacements))
                : []
        );
    }

    public static function keys(AbstractCollection $collection, $searchValue = null, bool $strict = false): self
    {
        if (func_num_args() === 1) {
            return static::fromArray(array_keys($collection->items));
        }

        $collection->validate($searchValue, null);

        return static::fromArray(array_keys($collection->items, $searchValue, $strict));
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
        AssertService::instance()->greater(
            0,
            new Argument($level, 2, 'level'),
            null,
            [AbstractCollection::class, 'foreach']
        );
        $this->recursiveForeach($this, $callback, $level);
    }

    public function push(...$items): int
    {
        $this->validateItems($items);

        return array_push($this->items, ...$items);
    }

    private static function collectionsToArrays(self ...$arrays): array
    {
        return array_map(static function (self $array): array {
            return $array->items;
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
                : array_map($callback, ...static::collectionsToArrays(...$collections))
        );
    }

    private function recursiveForeach($data, callable $callback, int $level, array $keys = []): void
    {
        if ($level === 0) {
            $callback($data, ...$keys);

            return;
        }

        foreach ($data as $key => $value) {
            $subKeys = $keys;
            $subKeys[] = $key;
            $this->recursiveForeach($value, $callback, $level - 1, $subKeys);
        }
    }

    public static function __set_state(array $items): self
    {
        return static::fromArray($items);
    }

    /** @return int|string */
    public function getRandomKey()
    {
        return array_rand($this->items);
    }

    public static function getRandomKeys(AbstractCollection $collection, int $number): self
    {
        return static::fromArray((array) array_rand($collection->items, $number));
    }

    /**
     * @param AbstractCollection $collection
     * @return $this
     */
    public static function fromValues(AbstractCollection $collection): self
    {
        return static::fromArray(array_values($collection->items));
    }

    public function values(): self
    {
        return static::fromValues($this);
    }

    /**
     * @param AbstractCollection ...$collections
     * @return $this
     */
    public function intersect(self ...$collections): self
    {
        return static::fromIntersect(function ($value1, $value2): int {
            return $value1 <=> $value2;
        }, false, $this, ...$collections);
    }

    /**
     * @param bool|callable $dataCompareFunc
     * @param bool|callable $keyCompareFunc
     * @param AbstractCollection ...$collections
     * @return $this
     */
    public static function fromIntersect($dataCompareFunc, $keyCompareFunc, self ...$collections): self
    {
        if (count($collections) === 0) {
            return static::fromArray();
        }

        if (count($collections) === 1) {
            return static::fromArray($collections[0]->items);
        }

        $arguments = static::collectionsToArrays(...$collections);

        if (!is_bool($dataCompareFunc)) {
            $arguments[] = $dataCompareFunc;
        }

        if (!is_bool($keyCompareFunc)) {
            $arguments[] = $keyCompareFunc;
        }

        if ($dataCompareFunc === true && $keyCompareFunc === true) {
            return static::fromArray(array_intersect_assoc(...$arguments));
        }

        if ($dataCompareFunc === false && $keyCompareFunc === true) {
            return static::fromArray(array_intersect_key(...$arguments));
        }

        if ($dataCompareFunc === true && $keyCompareFunc === false) {
            return static::fromArray(array_intersect(...$arguments));
        }

        if (is_bool($dataCompareFunc)) {
            return static::fromArray($dataCompareFunc
                ? array_intersect_uassoc(...$arguments)
                : array_intersect_ukey(...$arguments));
        }

        return static::fromArray($keyCompareFunc === true
            ? array_uintersect_assoc(...$arguments)
            : (
            $keyCompareFunc === false
                ? array_uintersect(...$arguments)
                : array_uintersect_uassoc(...$arguments)
            ));
    }

    public function splice(int $offset, ?int $length = null, ?AbstractCollection $replacement = null): self
    {
        $this->validateItems($replacement->items);

        return static::fromArray(
            array_splice($this->items, $offset, $length, $replacement->items ?? [])
        );
    }

    public static function flip(AbstractCollection $collection): self
    {
        return static::fromArray(array_flip($collection->items));
    }

    public function filter(?callable $callBack = null, ?int $mode = null): self
    {
        return static::fromArray(array_filter($this->items, $callBack, $mode ?? 0));
    }
}
