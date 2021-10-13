<?php declare(strict_types=1);
namespace EugeneErg\Graph\Services;

abstract class AbstractService
{
    private static $instances;
    final private function __construct() {}

    /** @return $this */
    final public static function instance(): self
    {
        if (!isset(static::$instances[static::class])) {
            static::$instances[static::class] = new static();
        }

        return static::$instances[static::class];
    }
}
