<?php declare(strict_types = 1);
namespace EugeneErg\Graph\ValueObjects\Collections;

/**
 * @see AbstractCollection::getCount()
 * @property-read int $count
 * @see AbstractCollection::getReverse()
 * @property-read AbstractCollection $reverse
 * @see AbstractCollection::getRandKey()
 * @property-read string|int $randKey
 * @see AbstractCollection::getValues()
 * @property-read AbstractCollection $values
 * @see AbstractCollection::getRecords()
 * @property-read array $records
 */
abstract class AbstractCollection implements \JsonSerializable, \Iterator, \ArrayAccess
{
    protected const COMPARE_FLAG_BY_KEY = 1;
    protected const COMPARE_FLAG_BY_KEY_AS_STRING = 3;
    protected const COMPARE_FLAG_BY_VALUE = 4;
    protected const COMPARE_FLAG_BY_VALUE_AS_STRING = 12;
    protected const COMPARE_FLAGS = self::COMPARE_FLAG_BY_VALUE;
    protected const SORT_TYPE = null;

    /** @var array */
    protected $records;

    public function __construct($records = [])
    {
        $this->records = $records;
    }

    public static function column(
        AbstractObjectCollection $collection,
        ?string $column = null,
        ?string $indexKey = null
    ): self {
        return static::create(array_column($collection->records, $column, $indexKey));
    }

    public static function fillKeys(StringCollection $keys, $value): self
    {
        return static::create(array_fill_keys($keys->records, $value));
    }

    public static function fill(int $startIndex, int $num, $value): self
    {
        return static::create(array_fill($startIndex, $num, $value));
    }

    /**
     * @param callable $callback
     * @param AbstractCollection ...$arrays
     * @return $this
     */
    public static function map(callable $callback, self ...$arrays): self
    {
        return static::create(
            count($arrays) ? array_map($callback, ...self::collectionsToArrays(...$arrays)) : []
        );
    }

    /**
     * @param callable $callback
     * @param AbstractCollection $collection
     * @return $this
     */
    public static function foreach(callable $callback, AbstractCollection $collection): self
    {
        $result = [];

        foreach ($collection->records as $key => $value) {
            $result[$key] = $callback($value, $key);
        }

        return static::create($result);
    }

    /**
     * @param AbstractCollection ...$arrays
     * @return $this
     */
    public static function merge(self ...$arrays): self
    {
        return static::create(array_merge(...self::collectionsToArrays(...$arrays)));
    }

    /**
     * @param AbstractCollection ...$collections
     * @return $this
     */
    public static function intersect(self ...$collections): self
    {
        $compareValue = self::hasCompareFlag(self::COMPARE_FLAG_BY_VALUE_AS_STRING)
            ? true : (
            self::hasCompareFlag(self::COMPARE_FLAG_BY_VALUE)
                ? [self::class, 'compareValue']
                : false
            );
        $compareKey = self::hasCompareFlag(self::COMPARE_FLAG_BY_KEY_AS_STRING)
            ? true : (
            self::hasCompareFlag(self::COMPARE_FLAG_BY_KEY)
                ? [self::class, 'isEqualKeys']
                : false
            );

        return static::create(self::intersectArrays(
            $compareValue,
            $compareKey,
            ...self::collectionsToArrays(...$collections)
        ));
    }

    public static function diff(self ...$collections): self
    {
        $compareValue = self::hasCompareFlag(self::COMPARE_FLAG_BY_VALUE_AS_STRING)
            ? true : (
                self::hasCompareFlag(self::COMPARE_FLAG_BY_VALUE)
                    ? [self::class, 'compareValue']
                    : false
            );
        $compareKey = self::hasCompareFlag(self::COMPARE_FLAG_BY_KEY_AS_STRING)
            ? true : (
                self::hasCompareFlag(self::COMPARE_FLAG_BY_KEY)
                    ? [self::class, 'isEqualKeys']
                    : false
            );

        return static::create(self::diffArrays(
            $compareValue,
            $compareKey,
            ...self::collectionsToArrays(...$collections)
        ));
    }

    public function slice(int $offset, ?int $length = null, bool $preserveKeys = false): self
    {
        return static::create(array_slice($this->records, $offset, $length, $preserveKeys));
    }

    /**
     * @param int $number
     * @return int|string|null
     */
    public function getKeyOfNumber(int $number)
    {
        $keyValue = array_slice($this->records, $number, 1, true);
        reset($keyValue);

        return key($keyValue);
    }

    /**
     * @param int $number
     * @return mixed
     */
    public function getValueOfNumber(int $number)
    {
        $keyValue = array_slice($this->records, $number, 1, true);

        return reset($keyValue);
    }

    public function has($needle, bool $strict = false): bool
    {
        return in_array($needle, $this->records, $strict);
    }

    public function hasKey(string $key): bool
    {
        return isset($this->records[$key]) || array_key_exists($key, $this->records);
    }

    /**
     * @param $needle
     * @param bool $strict
     * @return null|int|string
     */
    public function search($needle, bool $strict = false)
    {
        $result = array_search($needle, $this->records, $strict);

        return $result === false ? null : $result;
    }

    public function getReverse(bool $preserveKeys = false): self
    {
        return static::create(array_reverse($this->records, $preserveKeys));
    }

    /**
     * @return int|string
     * @throws \Exception
     */
    public function getRandKey()
    {
        if ($this->count === 0) {
            throw new \Exception('Second argument has to be between 1 and the number of elements in the array');
        }

        return array_rand($this->records);
    }

    public function getValues(?ScalarCollection $keys): self
    {
        if ($keys === null) {
            return static::create(array_values($this->records));
        }

        return self::map(function ($key) {
            return $this[$key];
        }, $keys);
    }

    public function changeKeyCase(int $case = CASE_LOWER): self
    {
        return static::create(array_change_key_case($this->records, $case));
    }

    public function current()
    {
        return current($this->records);
    }

    /** @return mixed|null */
    public function next()
    {
        $value = next($this->records);

        return $this->valid() ? $value : null;
    }

    /**
     * @return int|string|null
     */
    public function key()
    {
        return key($this->records);
    }

    public function valid(): bool
    {
        return $this->key() !== null;
    }

    /** @return mixed|null */
    public function rewind()
    {
        $value = reset($this->records);

        return $this->valid() ? $value : null;
    }

    public function __debugInfo(): array
    {
        return $this->records;
    }

    public function jsonSerialize(): array
    {
        return $this->records;
    }

    public function reduce(callable $callback, $initial = null)
    {
        foreach ($this->records as $key => $value) {
            $initial = $callback($initial, $value, $key);
        }

        return $initial;
    }

    public function filter(callable $callback, ?bool $addValueToArgument = true): self
    {
        return static::create(array_filter(
            $this->records,
            $callback,
            array_search($addValueToArgument, [
                0 => true,
                ARRAY_FILTER_USE_KEY => false,
                ARRAY_FILTER_USE_BOTH => null,
            ])
        ));
    }

    public function offsetExists($offset): bool
    {
        return isset($this->records[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->records[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        throw new \Exception('can\t mutable');
    }

    public function offsetUnset($offset): void
    {
        throw new \Exception('can\t mutable');
    }

    public function pad(int $count, $value): self
    {
        return static::create(array_pad(
            $this->records,
            $count + ($count > 0 ? $this->count : -$this->count),
            $value
        ));
    }

    public function unique(bool $preserveKeys = false): self
    {
        $result = [];

        foreach ($this->records as $key => $recordA) {
            foreach ($result as $recordB) {
                if (
                    (static::COMPARE_FLAGS === null && self::compareValue($recordA, $recordB) === 0)
                    || (static::COMPARE_FLAGS !== null && (string) $recordA === (string) $recordB)
                ) {
                    continue(2);
                }
            }

            $preserveKeys
                ? $result[$key] = $recordA
                : $result[] = $recordA;
        }

        return static::create($result);
    }

    public function combine(StringCollection $keys): self
    {
        return static::create(array_combine($keys->records, $this->records));
    }

    public function getRecords(): array
    {
        return $this->records;
    }

    public function __get(string $name)
    {
        $methodName = 'get' . ucfirst($name);

        if (method_exists($this, $methodName)) {
            return $this->{$methodName}();
        }

        throw new \Exception('property ' . $name . ' not exists');
    }

    public function __set(string $name, $value): void
    {
        $methodName = 'set' . ucfirst($name);

        if (method_exists($this, $methodName)) {
            $this->{$methodName}($value);

            return;
        }

        throw new \Exception('property ' . $name . ' not exists');
    }

    /**
     * @param array $records
     * @return $this
     */
    protected static function create(array $records = []): self
    {
        return new static($records);
    }

    protected static function compareValue($elementA, $elementB): int
    {
        return $elementA <=> $elementB;
    }

    protected static function isEqualKeys($keyA, $keyB): int
    {
        return $keyA <=> $keyB;
    }

    protected function getCount(): int
    {
        return count($this->records);
    }

    /**
     * @param bool|callable $dataCompareFunc
     * @param bool|callable $keyCompareFunc
     * @param array ...$arrays
     * @return array
     */
    private static function diffArrays($dataCompareFunc, $keyCompareFunc, array ...$arrays): array
    {
        $arguments = $arrays;

        if (!is_bool($dataCompareFunc)) {
            $arguments[] = $dataCompareFunc;
        }

        if (!is_bool($keyCompareFunc)) {
            $arguments[] = $keyCompareFunc;
        }

        if ($dataCompareFunc === true && $keyCompareFunc === true) {
            return array_diff_assoc(...$arguments);
        }

        if ($dataCompareFunc === false && $keyCompareFunc === true) {
            return array_diff_key(...$arguments);
        }

        if ($dataCompareFunc === true && $keyCompareFunc === false) {
            return array_diff(...$arguments);
        }

        if (is_bool($dataCompareFunc)) {
            return $dataCompareFunc
                ? array_diff_uassoc(...$arguments)
                : array_diff_ukey(...$arguments);
        }

        return $keyCompareFunc === true
            ? array_udiff_assoc(...$arguments)
            : (
            $keyCompareFunc === false
                ? array_udiff(...$arguments)
                : array_udiff_uassoc(...$arguments)
            );
    }

    /**
     * @param bool|callable $dataCompareFunc
     * @param bool|callable $keyCompareFunc
     * @param array ...$arrays
     * @return array
     */
    private static function intersectArrays($dataCompareFunc, $keyCompareFunc, array ...$arrays): array
    {
        $arguments = $arrays;

        if (!is_bool($dataCompareFunc)) {
            $arguments[] = $dataCompareFunc;
        }

        if (!is_bool($keyCompareFunc)) {
            $arguments[] = $keyCompareFunc;
        }

        if ($dataCompareFunc === true && $keyCompareFunc === true) {
            return array_intersect_assoc(...$arguments);
        }

        if ($dataCompareFunc === false && $keyCompareFunc === true) {
            return array_intersect_key(...$arguments);
        }

        if ($dataCompareFunc === true && $keyCompareFunc === false) {
            return array_intersect(...$arguments);
        }

        if (is_bool($dataCompareFunc)) {
            return $dataCompareFunc
                ? array_intersect_uassoc(...$arguments)
                : array_intersect_ukey(...$arguments);
        }

        return $keyCompareFunc === true
            ? array_uintersect_assoc(...$arguments)
            : (
            $keyCompareFunc === false
                ? array_uintersect(...$arguments)
                : array_uintersect_uassoc(...$arguments)
            );
    }

    private static function collectionsToArrays(self ...$arrays): array
    {
        return array_map(static function (self $array): array {
            return $array->records;
        }, $arrays);
    }

    private static function hasCompareFlag(int $flag): bool
    {
        return (static::COMPARE_FLAGS & $flag) === $flag;
    }

    public function isEqual(self $collection): bool
    {
        if ($collection->count !== $this->count) {
            return false;
        }

        $compareValue = self::hasCompareFlag(self::COMPARE_FLAG_BY_VALUE_AS_STRING)
            ? true : (
            self::hasCompareFlag(self::COMPARE_FLAG_BY_VALUE)
                ? [self::class, 'compareValue']
                : false
            );
        $compareKey = self::hasCompareFlag(self::COMPARE_FLAG_BY_KEY_AS_STRING)
            ? true : (
            self::hasCompareFlag(self::COMPARE_FLAG_BY_KEY)
                ? [self::class, 'isEqualKeys']
                : false
            );

        return count(self::diffArrays(
            $compareValue,
            $compareKey,
            ...self::collectionsToArrays($this, $collection)
        )) === 0;
    }
}
