<?php declare(strict_types=1);

namespace EugeneErg\Graph\Collections;

use ArrayAccess;
use Countable;
use Generator;
use IteratorAggregate;
use Traversable;

final class GeneratorCollection implements IteratorAggregate
{
    /** @var array|Traversable */
    private $items;

    /** @param array|Traversable $items */
    public function __construct($items = [])
    {
        $this->items = $items instanceof self ? $items->items : $items;
    }

    /**
     * @param callable $callback
     * @param array|Traversable ...$collections
     * @return $this
     */
    public function map(callable $callback, ...$collections): self
    {
        return self::fromMap($callback, $this, ...$collections);
    }

    /**
     * @param callable $callback
     * @param array|Traversable|self ...$collections
     * @return $this
     */
    public static function fromMap(callable $callback, ...$collections): self
    {
        if (count($collections) === 0) {
            return new self();
        }

        return self::multiForeach($collections, function (array $result) use ($callback): array {
            return [null, $callback($result)];
        });
    }

    public function filter(callable $callback): self
    {
        return $this->transform(function ($items) use ($callback): Generator {
            foreach ($items as $key => $value) {
                if ($callback($value, $key)) {
                    yield $key => $value;
                }
            }
        });
    }

    public function merge(...$collections): self
    {
        return self::fromMerge($this, ...$collections);
    }

    public static function fromMerge(...$collections): self
    {
        if (count($collections) < 2) {
            return new self(...$collections);
        }

        return self::fromCallback(function () use ($collections): Generator {
            foreach ($collections as $collection) {
                foreach ($collection as $key => $value) {
                    yield $key => $value;
                }
            }
        });
    }

    public function walk(callable $callback): self
    {
        return $this->transform(function ($items) use ($callback): Generator {
            foreach ($items as $key => $value) {
                yield $key => $callback($value, $key);
            }
        });
    }

    public function flip(): self
    {
        return $this->transform(function ($items): Generator {
            foreach ($items as $key => $value) {
                yield $value => $key;
            }
        });
    }

    public function values(): self
    {
        return $this->transform(function ($items): Generator {
            foreach ($items as $value) {
                yield $value;
            }
        });
    }

    public function walkRecursive(callable $callback): self
    {
        return self::fromWalkRecursive($this->items, $callback);
    }

    /** @param Traversable|array $items */
    private static function fromWalkRecursive($items, callable $callback): self
    {
        return self::fromCallback(function () use ($items, $callback): Generator {
            foreach ($items as $key => $value) {
                yield $key => $value instanceof Traversable || is_array($value)
                    ? self::fromWalkRecursive($value, $callback)
                    : $callback($value, $key);
            }
        });
    }

    public function transform(callable $callback): self
    {
        return new self($callback($this->items));
    }

    public function foreach(callable $callback): self
    {
        return $this->transform(function ($items) use ($callback): Generator {
            foreach ($items as $key => $value) {
                yield $callback($value, $key);
            }
        });
    }

    public static function fromCallback(callable $callback): self
    {
        return new self($callback());
    }

    public function getIterator(): Traversable
    {
        if ($this->items instanceof Traversable) {
            return $this->items;
        }

        foreach ($this->items as $key => $item) {
            yield $key => $item;
        }
    }

    public function changeKeys(callable $callback): self
    {
        $this->transform(function ($items) use ($callback): Generator {
            foreach ($items as $key => $value) {
                yield $callback($value, $key) => $value;
            }
        });
    }

    public function column(string $column, ?string $index = null): self
    {
        if ($index === null) {
            return $this->map(function ($item) use ($column) {
                return is_array($item) || $item instanceof ArrayAccess ? $item[$column] : $item->$column;
            });
        }

        return $this->transform(function ($items) use ($index, $column): Generator {
            foreach ($items as $item) {
                if (is_array($item) || $item instanceof ArrayAccess) {
                    yield $item[$index] => $item[$column];
                } else {
                    yield $item->$index => $item->$column;
                }
            }
        });
    }

    /** @param array|Traversable $replacement */
    public function splice(int $offset = 0, ?int $length = null, $replacement = []): self
    {
        $count = $offset < 0 || ($length !== null && $length < 0)
            ? count(is_array($this->items)
                ? $this->items
                : $this->getIteratorWithClass(Countable::class)
            )
            : null;

        if ($offset < 0) {
            $offset = $count + $offset;
        }

        if ($length !== null && $length < 0) {
            $length = $count - $offset + $length;
        }

        return $this->transform(function ($items) use ($offset, $length, $replacement): Generator {
            $count = 0;

            foreach ($items as $value) {
                if ($count === $offset + $length) {
                    foreach ($replacement as $value2) {
                        yield $value2;
                    }
                }

                if ($count < $offset || $count >= $offset + $length) {
                    yield $value;
                }

                $count++;
            }
        });
    }

    public function slice(int $offset = 0, ?int $length = null, bool $preserveKeys = false): self
    {
        return $this->transform(function ($items) use ($offset, $length, $preserveKeys): Generator {
            $count = 0;

            if ($preserveKeys) {
                foreach ($items as $key => $value) {
                    if ($count >= $offset) {
                        yield $key => $value;
                    }

                    if ($count === $offset + $length) {
                        break;
                    }

                    $count++;
                }
            } else {
                foreach ($items as $value) {
                    if ($count >= $offset) {
                        yield $value;
                    }

                    if ($count === $offset + $length) {
                        break;
                    }

                    $count++;
                }
            }
        });
    }

    public static function combine($keys, $values): self
    {
        return self::multiForeach([$keys, $values]);
    }

    private static function multiForeach(array $collections, ?callable $callback = null): self
    {
        return self::fromCallback(function () use ($callback, $collections): Generator {
            $result = [];
            array_walk($collections, function(&$collection) use (&$result) {
                $result[] = reset($collection);
            });

            while (count(array_filter($result)) !== 0) {
                [$key, $value] = $callback ? $callback($result) : $result;

                yield $key => $value;
                $result = [];
                array_walk($collections, function(&$collection) use (&$result) {
                    $result[] = next($collection);
                });
            }
        });
    }

    private function getIteratorWithClass(string $class): ?Traversable
    {
        $items = $this->items;

        while (!is_a($items, $class)) {
            if (!$items instanceof IteratorAggregate) {
                return null;
            }

            $items = $items->getIterator();
        }

        return $items;
    }

    public function keys($filterValue = null, bool $strict = false): self
    {
        if (func_num_args() === 0) {
            return $this->transform(function ($items): Generator {
                foreach ($items as $key => $value) {
                    yield $key;
                }
            });
        }

        if ($strict) {
            return $this->transform(function ($items) use ($filterValue): Generator {
                foreach ($items as $key => $value) {
                    if ($value === $filterValue) {
                        yield $key;
                    }
                }
            });
        }

        return $this->transform(function ($items) use ($filterValue): Generator {
            foreach ($items as $key => $value) {
                if ($value == $filterValue) {
                    yield $key;
                }
            }
        });
    }

    public function chunk(int $length, bool $preserveKeys = false): self
    {
        if ($preserveKeys) {
            return $this->transform(function ($items) use ($length): Generator {
                $result = [];

                foreach ($items as $key => $value) {
                    $result[$key] = $value;

                    if (count($result) === $length) {
                        yield $result;
                        $result = [];
                    }
                }

                if (count($result) !== 0) {
                    yield $result;
                }
            });
        }

        return $this->transform(function ($items) use ($length): Generator {
            $result = [];

            foreach ($items as $value) {
                $result[] = $value;

                if (count($result) === $length) {
                    yield $result;
                    $result = [];
                }
            }

            if (count($result) !== 0) {
                yield $result;
            }
        });
    }

    public function fillKeys($value): self
    {
        return $this->transform(function ($items) use ($value): Generator {
            foreach ($items as $key => $value2) {
                yield $key => $value;
            }
        });
    }

    public static function fromFill(int $start, int $count, $value): self
    {
        return self::fromCallback(function () use ($start, $count, $value): Generator {
            for ($i = 0; $i < $count; $i++) {
                yield $i + $start => $value;
            }
        });
    }

    public static function fromRange(string $start, string $end, float $step = 1): self
    {
        $numbers = is_numeric($start) || is_numeric($end);
        $value = $numbers ? (is_numeric($start) ? $start : 0) : ord($start);
        $end = $numbers ? (is_numeric($end) ? $end : 0) : ord($end);
        $plus = $start <= $end;
        $walker = $plus ? $step : -$step;

        if ($plus && $numbers) {
            return self::fromCallback(function () use ($value, $end, $walker): Generator {
                for (; $value <= $end; $value += $walker) {
                    yield $value;
                }
            });
        }

        if ($plus) {
            return self::fromCallback(function () use ($value, $end, $walker): Generator {
                for (; $value <= $end; $value += $walker) {
                    yield chr($value);
                }
            });
        }

        if ($numbers) {
            return self::fromCallback(function () use ($value, $end, $walker): Generator {
                for (; $value >= $end; $value += $walker) {
                    yield $value;
                }
            });
        }

        return self::fromCallback(function () use ($value, $end, $walker): Generator {
            for (; $value >= $end; $value += $walker) {
                yield chr($value);
            }
        });
    }
}
