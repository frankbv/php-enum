<?php

namespace Frank;

use InvalidArgumentException;
use ReflectionClass;
use UnexpectedValueException;

/**
 * Extend this class and add some constants to make it work
 * Please do add method annotations as well
 * Example: (@)method static Enum FOO()
 */
abstract class Enum
{
    /**
     * Cached array of arrays with constants for each parent class
     * indexed by classname
     * @var mixed[]
     */
    private static $constants = [];
    /**
     * Cache of enums retrieved through static calls
     *
     * @var Enum[][]
     */
    private static $cache;

    /**
     * @var mixed the internal value
     */
    private $value;

    /**
     * @param mixed $value
     */
    public function __construct($value)
    {
        if (!static::isValidValue($value)) {
            throw new InvalidArgumentException(sprintf('%s is not a valid value for %s', $value, static::class));
        }
        $this->value = $value;
    }

    /**
     * @return mixed[] array of constants name => value
     */
    public static function getConstants()
    {
        $classname = static::class;
        if (!isset(self::$constants[$classname])) {
            $reflection = new ReflectionClass($classname);
            self::$constants[$classname] = $reflection->getConstants();
        }
        return self::$constants[$classname];
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public static function isValidValue($value): bool
    {
        return in_array($value, self::getConstants(), true);
    }

    /**
     * @return static[]
     */
    public static function all(): array
    {
        $classname = static::class;
        if (!isset(self::$cache[$classname])) {
            self::$cache[$classname] = array_map(function ($value) {
                return new static($value);
            }, static::getConstants());
        }

        return self::$cache[$classname];
    }

    /**
     * @return mixed
     */
    public function value()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public function is($value): bool
    {
        return $this->value === $value;
    }

    /**
     * @param mixed $other
     * @return bool
     */
    public function equals($other): bool
    {
        return $other instanceof $this && $other->value() === $this->value();
    }

    /**
     * @param mixed $other
     * @throws UnexpectedValueException
     * @throws InvalidArgumentException
     */
    public function assertEquals($other)
    {
        if (!$this->equals($other)) {
            if ($other instanceof $this) {
                throw new UnexpectedValueException(sprintf(
                    'Failed to assert %s equals %s',
                    $this->value(),
                    $other->value()
                ));
            } else {
                throw new InvalidArgumentException(sprintf(
                    'Cannot compare %s to %s',
                    is_object($other) ? get_class($other) : gettype($other),
                    static::class
                ));
            }
        }
    }

    public function __toString(): string
    {
        return (string)$this->value();
    }

    /**
     * Makes it possible to easily instantiate an Enum by statically calling the
     * constant name to
     * @param $name
     * @param $arguments
     * @return static
     */
    public static function __callStatic($name, $arguments)
    {
        return self::getCached($name);
    }

    private static function getCached(string $name): Enum
    {
        $classname = static::class;
        if (!isset(self::$cache[$classname])) {
            static::all();
        }

        return self::$cache[$classname][$name];
    }
}
