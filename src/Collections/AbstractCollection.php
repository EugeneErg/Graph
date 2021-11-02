<?php declare(strict_types = 1);
namespace EugeneErg\Graph\Collections;

use ArrayAccess;
use Countable;
use Error;
use EugeneErg\Graph\Enums\CollectionFilterEnum;
use EugeneErg\Graph\Services\Assert\Argument;
use EugeneErg\Graph\Services\AssertService;
use EugeneErg\Graph\ValueObjects\AbstractValueObject;
use Iterator;
use JsonSerializable;
use Throwable;

/**
 * @see AbstractCollection::fromValues()
 * @method $this values()
 * @see AbstractCollection::fromFilter()
 * @method $this filter(callable|null $callBack = null, int|null $mode = null)
 * @see AbstractCollection::fromUnique()
 * @method $this unique(callable|null $callback = null)
 * @see AbstractCollection::fromKeys()
 * @method $this keys(mixed|null $searchValue = null, bool $strict = false)
 * @see AbstractCollection::fromWalk()
 * @method $this walk(callable $callback)
 * @see AbstractCollection::fromRandomKeys()
 * @method $this randomKeys(int $number)
 * @see AbstractCollection::fromFlip()
 * @method $this flip()
 */
abstract class AbstractCollection extends AbstractValueObject implements JsonSerializable, Iterator, ArrayAccess, Countable
{
    protected const ELEMENT_CLASS = null;

    /** @var array */
    private $items;

    public function __construct(array $items = [])
    {
        $this->validateItems($items);
        $this->items = $items;
    }

    private function setRecursive($items, $value, ...$keys)
    {
        if (!count($keys)) {
            return $value;
        }

        $key = array_pop($keys);

        return $items instanceof AbstractCollection
            ? $items->set($value, $key, ...$keys)
            : [$key => $this->setRecursive($items[$key] ?? null, $value, ...$keys)];
    }

    /** @return $this */
    protected static function fromArray(array $items = [], bool $filtered = false): self
    {
        return new static(
            $filtered ? array_filter($items, [static::class, 'isValid'], ARRAY_FILTER_USE_BOTH) : $items
        );
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

    public function end()
    {
        $value = end($this->items);

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

    /**
     * @param int|string|null $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value): void
    {
        $this->set($value, $offset);
    }

    /**
     * @param mixed $value
     * @param int|string|null $key
     * @param int|string|null ...$keys
     * @return $this
     */
    public function set($value, $key, ...$keys): self
    {
        if ($key === null) {
            $key = static::getNextKey($this->items);
        }

        $value = $this->setRecursive(
            $key === null || !isset($this->items[$key]) ? null : $this->items[$key],
            $value,
            ...$keys
        );
        $class = static::ELEMENT_CLASS;

        if (is_a($class, AbstractCollection::class, true) && is_array($value)) {
            $value = $class::fromRecursiveArray($value);
        }

        $this->validate($value, $key);
        $key === null
            ? $this->items[] = $value
            : $this->items[$key] = $value;

        return $this;
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

    public static function validateKey($key): void
    {
    }

    private function validate($value, $key): void
    {
        static::validateKey($key);
        static::validateElement($value);
    }

    public static function isValid($value, $key): bool
    {
        return static::isValidElement($value) && ($key === null || static::isValidKey($key));
    }

    public static function isValidElement($value): bool
    {
        try {
            static::validateElement($value);

            return true;
        } catch (Throwable $exception) {
            return false;
        }
    }

    public static function validateElement($value): void
    {
        AssertService::instance()->type(static::ELEMENT_CLASS, $value);
    }

    public static function isValidKey($key): bool
    {
        try {
            static::validateKey($key);

            return true;
        } catch (Throwable $exception) {
            return false;
        }
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
    public static function fromReplace(bool $filtered = false, self ...$replacements): self
    {
        return static::fromArray(
            count($replacements) > 0
                ? array_replace(...self::collectionsToArrays(...$replacements))
                : [],
            $filtered
        );
    }

    /**
     * @param AbstractCollection ...$replacements
     * @return $this
     */
    public function replace(self ...$replacements): self
    {
        return static::fromReplace(false, $this, ...$replacements);
    }

    /**
     * @param AbstractCollection ...$replacements
     * @return $this
     */
    public static function fromMerge(bool $filtered = false, self ...$replacements): self
    {
        return static::fromArray(
            count($replacements) > 0
                ? array_merge(...self::collectionsToArrays(...$replacements))
                : [],
            $filtered
        );
    }

    /**
     * @param AbstractCollection ...$replacements
     * @return $this
     */
    public function merge(self ...$replacements): self
    {
        return static::fromMerge(false, $this, ...$replacements);
    }

    public static function fromKeys(
        AbstractCollection $collection,
        $searchValue = null,
        bool $strict = false,
        bool $filtered = false
    ): self {
        if (func_num_args() === 1) {
            return static::fromArray(array_keys($collection->items), $filtered);
        }

        $collection->validate($searchValue, null);

        return static::fromArray(array_keys($collection->items, $searchValue, $strict), $filtered);
    }

    /**
     * @param AbstractCollection $collection
     * @param callable $callback
     * @param bool $filtered
     * @return $this
     */
    public static function fromWalk(
        AbstractCollection $collection,
        callable $callback,
        bool $filtered = false
    ): self {
        $array = $collection->toArray();
        array_walk($array, function (&$item, $key) use ($callback) {
            $item = $callback($item, $key);
        });

        return static::fromArray($array, $filtered);
    }

    public function foreach(callable $callback, int $level = 1): void
    {
        static::fromForeach($this, $callback, $level, true);
    }

    /**
     * @param AbstractCollection $collection
     * @param callable $callback
     * @param int $level
     * @return static
     */
    public static function fromForeach(
        self $collection,
        callable $callback,
        int $level = 1,
        bool $filtered = false
    ): self {
        AssertService::instance()->greater(
            0,
            new Argument($level, 3, 'level'),
            null,
            [AbstractCollection::class, 'fromForeach']
        );

        return static::fromArray(static::recursiveForeach(
            $collection,
            $callback,
            $level,
            $filtered
        ));
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
    public static function fromMap(callable $callback, bool $filtered = false, AbstractCollection ...$collections): self
    {
        return static::fromArray(
            count($collections) === 0
                ? []
                : array_map($callback, ...static::collectionsToArrays(...$collections)),
            $filtered
        );
    }

    public function map(callable $callback, AbstractCollection ...$collections): self
    {
        return static::fromMap($callback, false, $this, ...$collections);
    }

    private static function recursiveForeach(
        $data,
        callable $callback,
        int $level,
        bool $filtered,
        array $keys = [],
        array &$result = []
    ): array {
        if ($data instanceof AbstractCollection) {
            $data = $data->items;
        }

        foreach ($data as $key => $value) {
            $nextKeys = $keys;
            $nextKeys[] = $key;

            if ($level > 1) {
                static::recursiveForeach(
                    $value,
                    $callback,
                    $level - 1,
                    $filtered,
                    $nextKeys,
                    $result
                );
            } else {
                $value = $callback($value, ...$nextKeys);

                if (!$filtered || static::isValidElement($value)) {
                    $key = static::getNextKey($result);
                    $key === null ? $result[] = $value : $result[$key] = $value;
                }
            }
        }

        return $result;
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

    public static function fromRandomKeys(AbstractCollection $collection, int $number, bool $filtered = false): self
    {
        return static::fromArray((array) array_rand($collection->items, $number), $filtered);
    }

    /**
     * @param AbstractCollection $collection
     * @return $this
     */
    public static function fromValues(AbstractCollection $collection, bool $filtered = false): self
    {
        return static::fromArray(array_values($collection->items), $filtered);
    }

    /**
     * @param AbstractCollection ...$collections
     * @return $this
     */
    public function intersect(self ...$collections): self
    {
        return static::fromIntersect(function ($value1, $value2): int {
            return $value1 <=> $value2;
        }, false, false, $this, ...$collections);
    }

    /**
     * @param bool|callable $dataCompareFunc
     * @param bool|callable $keyCompareFunc
     * @param AbstractCollection ...$collections
     * @return $this
     */
    public static function fromIntersect(
        $dataCompareFunc,
        $keyCompareFunc,
        bool $filtered = false,
        self ...$collections
    ): self {
        return static::fromIntersectOrDifference(
            [
                'array_intersect_assoc',
                'array_intersect_key',
                'array_intersect',
                'array_intersect_uassoc',
                'array_intersect_ukey',
                'array_uintersect_assoc',
                'array_uintersect',
                'array_uintersect_uassoc',
            ],
            $dataCompareFunc,
            $keyCompareFunc,
            $filtered,
            ...$collections
        );
    }

    private static function fromIntersectOrDifference(
        array $callbacks,
        $dataCompareFunc,
        $keyCompareFunc,
        bool $filtered = false,
        self ...$collections
    ): self {
        if (count($collections) === 0) {
            return static::fromArray();
        }

        if (count($collections) === 1) {
            return static::fromArray($collections[0]->items, $filtered);
        }

        $arguments = static::collectionsToArrays(...$collections);

        if (!is_bool($dataCompareFunc)) {
            $arguments[] = $dataCompareFunc;
        }

        if (!is_bool($keyCompareFunc)) {
            $arguments[] = $keyCompareFunc;
        }

        if ($dataCompareFunc === true && $keyCompareFunc === true) {
            return static::fromArray($callbacks[0](...$arguments), $filtered);
        }

        if ($dataCompareFunc === false && $keyCompareFunc === true) {
            return static::fromArray($callbacks[1](...$arguments), $filtered);
        }

        if ($dataCompareFunc === true && $keyCompareFunc === false) {
            return static::fromArray($callbacks[2](...$arguments), $filtered);
        }

        if (is_bool($dataCompareFunc)) {
            return static::fromArray($dataCompareFunc
                ? $callbacks[3](...$arguments)
                : $callbacks[4](...$arguments), $filtered);
        }

        return static::fromArray($keyCompareFunc === true
            ? $callbacks[5](...$arguments)
            : (
            $keyCompareFunc === false
                ? $callbacks[6](...$arguments)
                : $callbacks[7](...$arguments)
            ), $filtered);
    }

    /**
     * @param AbstractCollection ...$collections
     * @return $this
     */
    public function difference(self ...$collections): self
    {
        return static::fromDifference(function ($value1, $value2): int {
            return $value1 <=> $value2;
        }, false, false, $this, ...$collections);
    }

    /**
     * @param bool|callable $dataCompareFunc
     * @param bool|callable $keyCompareFunc
     * @param AbstractCollection ...$collections
     * @return $this
     */
    public static function fromDifference($dataCompareFunc, $keyCompareFunc, bool $filtered = false, self ...$collections): self
    {
        return static::fromIntersectOrDifference(
            [
                'array_diff_assoc',
                'array_diff_key',
                'array_diff',
                'array_diff_uassoc',
                'array_diff_ukey',
                'array_udiff_assoc',
                'array_udiff',
                'array_udiff_uassoc',
            ],
            $dataCompareFunc,
            $keyCompareFunc,
            $filtered,
            ...$collections
        );
    }

    public function splice(int $offset, ?int $length = null, ?AbstractCollection $replacement = null): self
    {
        $this->validateItems($replacement->items);

        return static::fromArray(
            array_splice($this->items, $offset, $length, $replacement->items ?? [])
        );
    }

    public static function fromFlip(AbstractCollection $collection, bool $filtered = false): self
    {
        return static::fromArray(array_flip($collection->items), $filtered);
    }

    /** @return $this */
    public static function fromFilter(
        self $collection,
        ?callable $callBack = null,
        ?CollectionFilterEnum $mode = null
    ): self {
        return static::fromArray(
            array_filter($collection->items, $callBack, ($mode ?? CollectionFilterEnum::VALUE())->getValue())
        );
    }

    public function implode(string $string): string
    {
        return implode($string, $this->items);
    }

    public function unshift(...$values): void
    {
        $this->validateItems($values);
        array_unshift($this->items, ...$values);
    }

    public function __call(string $method, array $arguments): self
    {
        $fromMethod = 'from' . ucfirst($method);

        if (method_exists($this, $fromMethod)) {
            return call_user_func([static::class, $fromMethod], $this, ...$arguments);
        }

        throw new Error(vsprintf('Call to undefined method %s::%s()', [static::class, $method]));
    }

    public static function fromUnique(self $collection, ?callable $callback = null, bool $filtered = false): self
    {
        $result = [];
        $callback = $callback ?? function ($valueA, $valueB) {
            return $valueA == $valueB;
        };

        foreach ($collection->items as $key => $valueA) {
            if ($filtered && !static::isValid($valueA, $key)) {
                continue;
            }

            foreach ($result as $valueB) {
                if ($callback($valueA, $valueB)) {
                    break;
                }
            }

            $result[$key] = $valueA;
        }

        return static::fromArray($result);
    }

    public static function fromFillKeys(self $keys, $value, bool $filtered = false): self
    {
        return static::fromArray(array_fill_keys($keys->items, $value), $filtered);
    }

    /**
     * @param array $items
     * @return string|int|null
     */
    public static function getNextKey(array $items)
    {
        return null;
    }

    /** @return $this */
    public static function fromRecursiveArray(array $items = []): self
    {
        $class = static::ELEMENT_CLASS;

        if (is_a($class, AbstractCollection::class, true)) {
            foreach ($items as $key => $item) {
                $result[$key] = $item instanceof AbstractCollection
                    ? $item : $class::fromRecursiveArray($item);
            }
        }

        return static::fromArray($items);
    }
}
