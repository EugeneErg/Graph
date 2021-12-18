<?php declare(strict_types=1);

namespace EugeneErg\Graph\Collections;

use ArrayAccess;
use Error;
use EugeneErg\Graph\Collections\Iterator\Iterator;
use EugeneErg\Graph\Collections\Iterator\IteratorItem;
use EugeneErg\Graph\Services\Assert\Argument;
use EugeneErg\Graph\Services\AssertService;
use EugeneErg\Graph\Traits\AttributeTrait;
use Generator;
use IteratorAggregate;
use JsonSerializable;
use Throwable;
use Traversable;

/**
 * @see AbstractCollection2::fromWalkRecursive()
 * @method $this walkRecursive(callable $callback, bool $filtered = false)
 * @see AbstractCollection2::fromValues()
 * @method $this values()
 * @see AbstractCollection2::fromUnique()
 * @method $this unique(callable|null $callback = null)
 * @see AbstractCollection2::fromKeys()
 * @method $this keys(mixed|null $searchValue = null, bool $strict = false)
 * @see AbstractCollection2::fromFlip()
 * @method $this flip()
 * @see AbstractCollection2::fromSlice()
 * @method $this slice(int $offset, ?int $length = null, bool $preserveKeys = false, bool $filtered = false)
 * @see AbstractCollection2::fromFillKeys()
 * @method $this fillKeys($value)
 */
class AbstractCollection2 implements IteratorAggregate, JsonSerializable
{
    use AttributeTrait;

    protected const ELEMENT_CLASS = null;
    private array $items;

    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    public function getIterator(): Generator
    {
        foreach ($this->items as $key => $value) {
            yield $key => $value;
        }
    }

    /**
     * @param string|int $key
     * @param string|int ...$keys
     */
    protected function get($key, ...$keys)
    {
        array_unshift($keys, $key);
        $items = $this->items;

        foreach ($keys as $number => $key) {
            $items = $items[$key];

            if ($items instanceof AbstractCollection2) {
                return count($keys) - $number > 1
                    ? $items->get(...array_slice($keys, $number + 1))
                    : $items->getIterator();
            }
        }

        return $items;
    }

    /**
     * @param string|int $key
     * @param string|int ...$keys
     */
    protected function isset($key, ...$keys): bool
    {
        array_unshift($keys, $key);
        $items = $this->items;

        foreach ($keys as $number => $key) {
            if (!isset($items[$key])) {
                return false;
            }

            $items = $items[$key];

            if ($items instanceof AbstractCollection2 && count($keys) - $number > 1) {
                return $items->isset(...array_slice($keys, $number + 1));
            }
        }

        return true;
    }

    /**
     * @param string|int $key
     * @param string|int ...$keys
     */
    protected function unset($key, ...$keys): void
    {
        array_unshift($keys, $key);
        $items = &$this->items;
        $lastKey = array_pop($keys);

        foreach ($keys as $number => $key) {
            if (!isset($items[$key])) {
                return;
            }

            $items = &$items[$key];

            if ($items instanceof AbstractCollection2) {
                $keys = array_slice($keys, $number + 1);
                $keys[] = $lastKey;
                $items->unset(...$keys);

                return;
            }
        }

        unset($items[$lastKey]);
    }

    /**
     * @param string|int|null $key
     * @param string|int|null ...$keys
     * @param mixed $value
     */
    protected function set($key, $keys, $value = null)
    {
        $keys = func_get_args();
        $value = array_pop($keys);
        $firstKey = array_shift($keys);

        if ($firstKey === null) {
            $firstKey = static::getNextKey($this->items);
        }

        if (!count($keys)) {
            $firstKey === null
                ? $this->items[] = static::createChildElement($value)
                : $this->items[$firstKey] = static::createChildElement($value);
        } else {
            $lastKey = array_pop($keys);
            $firstKey === null
                ? $this->items[] = static::createChildElement(
                    $this->createArrayValue($keys, $lastKey, $value)
                )
                : $this->items[$firstKey] = static::createChildElement(
                    $this->createArrayValue($keys, $lastKey, $value, $this->items[$firstKey] ?? [])
                );
        }

        return $value;
    }

    public static function createChildElement($value, bool $filtered = false)
    {
        if (static::isValidElement($value)) {
            return $value;
        }

        $class = static::ELEMENT_CLASS;

        if (is_a($class, AbstractCollection2::class, true)) {
            return $class::fromRecursiveArray($value, $filtered);
        }

        if (!$filtered) {
            throw new Error('cannot create new child element');
        }

        return null;
    }

    private function createArrayValue(array $keys, $lastKey, $value, $root = [])
    {
        if (is_object($root)) {
            $root = clone $root;
        }

        if ($root instanceof self) {
            $root->set($lastKey, $value);

            return $root;
        }

        $branch = &$root;

        foreach ($keys as $number => $key) {
            if ($key === null) {
                $key = static::getNextKey($branch);
            }

            if (!isset($branch[$key])) {
                $branch[$key] = [];
            } elseif ($branch[$key] instanceof self) {
                $keys = array_slice($keys, $number + 1);
                $keys[] = $lastKey;
                $keys[] = $value;
                $branch[$key]->set(...$keys);

                return $root;
            } elseif (!is_array($branch[$key]) || !$branch[$key] instanceof ArrayAccess) {
                $branch[$key] = [];
            }
        }

        $branch[$lastKey] = $value;

        return $root;
    }

    public static function validateElement($value): void
    {
        AssertService::instance()->type(static::ELEMENT_CLASS, $value);
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

    /** @return $this */
    public static function fromRecursiveArray(array $items = [], bool $filtered = false): self
    {
        array_walk($items, function (&$item) use ($filtered): void {
            $item = static::createChildElement($item, $filtered);
        });

        return static::fromArray($items, $filtered);
    }

    /** @return $this */
    public static function fromArray(array $items = [], bool $filtered = false): self
    {
        return new static(
            $filtered ? array_filter($items, [static::class, 'isValid'], ARRAY_FILTER_USE_BOTH) : $items
        );
    }

    public function __clone()
    {
        foreach ($this->items as $key => $item) {
            if (is_object($item)) {
                $this->items[$key] = clone $item;
            }
        }
    }

    public static function isValid($value, $key): bool
    {
        return static::isValidElement($value) && ($key === null || static::isValidKey($key));
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

    public static function validateKey($key): void
    {
    }



    public function getKeyValueByPosition(int $position): ?array
    {
        if ($position < 0) {
            $position = $this->count() + $position;
        }

        $result = array_slice($this->items, $position, 1, true);
        $value = reset($result);
        $key = key($result);

        return $key === null ? null : [$key, $value];
    }

    public function getKeyByPosition(int $position = -1)
    {
        return $this->getKeyValueByPosition($position)[0] ?? null;
    }

    public function getValueByPosition(int $position)
    {
        return $this->getKeyValueByPosition($position)[1] ?? null;
    }

    public function count(): int
    {
        return count($this->items);
    }

    /**
     * @param callable|int|string|Traversable|array $callback
     * @param callable|int|string|Traversable|array ...$callbacks
     * @return Generator|Iterator[]|IteratorItem[][]
     */
    public function listBy($callback = 1, ...$callbacks): Generator
    {
        return self::createGeneratorBy($this, $callback, ...$callbacks);
    }

    /**
     * @param Traversable|array $data
     * @param callable|int|string|Traversable|array $callback
     * @param callable|int|string|Traversable|array ...$callbacks
     * @return Generator|Iterator[]|IteratorItem[][]
     */
    private static function createGeneratorBy($data, $callback, ...$callbacks): Generator
    {
        if (is_int($callback)) {
            if ($callback > 1) {
                array_unshift($callbacks, $callback - 1);
            }

            $callback = fn($value) => $value;
        } elseif (!is_callable($callback) && is_string($callback)) {
            $callback = fn($value) => is_object($value) ? $value->$callback : $value[$callback];
        } elseif ((!is_callable($callback) && is_array($callback)) || $callback instanceof Traversable) {
            $callback = fn() => $callback;
        }

        if (count($callbacks) > 0) {
            foreach ($data as $key => $value) {
                foreach (self::createGeneratorBy($callback($value), ...$callbacks) as $value2) {
                    $value2->unshift([$key => $value]);

                    yield $value2;
                }
            }
        } else {
            foreach ($data as $key => $value) {
                yield new Iterator([$key => $value]);
            }
        }
    }

    /** @return $this */
    public static function fromWalkRecursive(
        AbstractCollection2 $collection,
        callable $callback,
        bool $filtered = false
    ): self {
        $array = $collection->toArrayRecursive();
        array_walk_recursive($array, function (&$item, $key) use ($callback) {
            $item = $callback($item, $key);
        });

        return static::fromRecursiveArray($array, $filtered);
    }

    public function toArrayRecursive(): array
    {
        return self::staticToArrayRecursive($this);
    }

    private static function staticToArrayRecursive($data)
    {
        if (is_array($data)) {
            return array_map(function ($item) {
                return self::staticToArrayRecursive($item);
            }, $data);
        }

        foreach (['toArray', '__debugInfo', '__sleep', '__serialize'] as $method) {
            if (method_exists($data, $method)) {
                return self::staticToArrayRecursive(call_user_func([$data, $method]));
            }
        }

        if ($data instanceof JsonSerializable) {
            return self::staticToArrayRecursive($data->jsonSerialize());
        }

        if (is_object($data)) {
            return self::staticToArrayRecursive((array) $data);
        }

        return $data;
    }

    public function __call(string $method, array $arguments): self
    {
        $fromMethod = 'from' . ucfirst($method);

        if (method_exists($this, $fromMethod)) {
            return call_user_func([static::class, $fromMethod], $this, ...$arguments);
        }

        throw new Error(vsprintf('Call to undefined method %s::%s()', [static::class, $method]));
    }

    /**
     * @param array $items
     * @return string|int|null
     */
    public static function getNextKey(array $items)
    {
        return null;
    }

    public function toArray(): array
    {
        return $this->items;
    }

    public function jsonSerialize()
    {
        return $this->items;
    }

    /** @return $this */
    public static function fromKeys(
        AbstractCollection2 $collection,
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

    private function validate($value, $key): void
    {
        static::validateKey($key);
        static::validateElement($value);
    }

    /**
     * @param AbstractCollection2 ...$replacements
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

    private static function collectionsToArrays(self ...$arrays): array
    {
        return array_map(static function (self $array): array {
            return $array->items;
        }, $arrays);
    }

    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }

    /** @return $this */
    public static function fromValues(AbstractCollection2 $collection, bool $filtered = false): self
    {
        return static::fromArray(array_values($collection->items), $filtered);
    }

    /**
     * @param AbstractCollection2 $collection
     * @param callable $callback
     * @param bool|callable $preserveKeys
     * @param bool $filtered
     * @return static
     */
    public static function fromRecursive(
        AbstractCollection2 $collection,
        callable $callback,
        $preserveKeys = false,
        bool $filtered = false
    ): self {
        AssertService::instance()
            ->type(['boolean', 'callable'], new Argument($preserveKeys, 3, 'preserveKeys'));
        $result = [];
        static::fromRecursiveData($collection, $callback, $preserveKeys, $result);

        return self::fromArray($result, $filtered);
    }

    private static function fromRecursiveData($data, callable $callback, $preserveKeys, array &$result): void
    {
        foreach ($data as $key => $item) {
            if ($item instanceof Traversable || is_array($item)) {
                static::fromRecursiveData($item, $callback, $preserveKeys, $result);
            } elseif ($preserveKeys === true) {
                $result[$key] = $callback($item, $key);
            } elseif ($preserveKeys === false) {
                $result[] = $callback($item, $key);
            } else {
                $result[$preserveKeys($item, $key)] = $callback($item, $key);
            }
        }
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

    /**
     * @param bool|callable $dataCompareFunc
     * @param bool|callable $keyCompareFunc
     * @param AbstractCollection2 ...$collections
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

    /** @return $this */
    public function difference(self ...$collections): self
    {
        return static::fromDifference(function ($value1, $value2): int {
            return $value1 <=> $value2;
        }, false, false, $this, ...$collections);
    }

    public function __debugInfo(): array
    {
        return $this->toArray();
    }

    /** @return $this */
    public static function fromMap(
        callable $callback,
        bool $filtered = false,
        AbstractCollection2 ...$collections
    ): self {
        return static::fromArray(
            count($collections) === 0
                ? []
                : array_map($callback, ...static::collectionsToArrays(...$collections)),
            $filtered
        );
    }

    public function map(callable $callback, AbstractCollection2 ...$collections): self
    {
        return static::fromMap($callback, false, $this, ...$collections);
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

    public function implode(string $string): string
    {
        return implode($string, $this->items);
    }

    public function getUpdatingIterator(): Traversable
    {
        for (
            $value = reset($this->items);
            ($key = key($this->items)) !== null;
            $value = next($this->items)
        ) {
            yield $key => $value;
        }
    }

    /** @return int|string */
    public function getRandomKey()
    {
        return array_rand($this->items);
    }

    /** @return $this */
    public static function fromFlip(AbstractCollection2 $collection, bool $filtered = false): self
    {
        return static::fromArray(array_flip($collection->items), $filtered);
    }

    public static function fromSlice(
        AbstractCollection2 $collection,
        int $offset,
        ?int $length = null,
        bool $preserveKeys = false,
        bool $filtered = false
    ): self {
        return static::fromArray(array_slice($collection->items, $offset, $length, $preserveKeys), $filtered);
    }

    /** @return $this */
    public function replace(self ...$replacements): self
    {
        return static::fromReplace(false, $this, ...$replacements);
    }

    /** @return $this */
    public static function fromFillKeys(AbstractCollection2 $keys, $value, bool $filtered = false): self
    {
        return static::fromArray(array_fill_keys($keys->items, $value), $filtered);
    }

    /** @return $this */
    public static function fromWalk(
        AbstractCollection2 $collection,
        callable $callback,
        bool $filtered = false
    ): self {
        $array = $collection->toArray();
        array_walk($array, function (&$item, $key) use ($callback) {
            $item = $callback($item, $key);
        });

        return static::fromArray($array, $filtered);
    }

    public function splice(
        int $offset,
        ?int $length = null,
        ?AbstractCollection2 $replacement = null,
        bool $filtered = false
    ): self {
        $this->validateItems($replacement->items ?? []);

        return static::fromArray(array_splice(
            $this->items,
            $offset,
            $length ?? $this->count(),
            $replacement->items ?? []
        ), $filtered);
    }

    private function validateItems(array $items): void
    {
        array_walk($items, [$this, 'validate']);
    }

    /** @return $this */
    public static function fromMerge(bool $filtered = false, self ...$replacements): self
    {
        return static::fromArray(
            count($replacements) > 0
                ? array_merge(...self::collectionsToArrays(...$replacements))
                : [],
            $filtered
        );
    }
}
