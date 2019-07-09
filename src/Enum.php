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
    private static $cache = [];

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
        return array_map(function ($value) {
            return static::of($value);
        }, static::getConstants());
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
     * @param mixed $value
     * @return static
     */
    public final static function of($value): self
    {
        $className = static::class;
        if (!isset(self::$cache[$className][$value])) {
            self::$cache[$className][$value] = new static($value);
        }
        return self::$cache[$className][$value];
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
        $constants = static::getConstants();
        if (!isset($constants[$name])) {
            throw new InvalidArgumentException(sprintf('%s does not exist in %s', $name, static::class));
        }

        return static::of($constants[$name]);
    }
}
