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
     * @var mixed the internal value
     */
    private $value;

    /**
     * @param mixed $value
     */
    public function __construct($value)
    {
        if (!self::isValidValue($value)) {
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
     * @return Enum[]
     */
    public static function all(): array
    {
        return array_map(function ($value) {
            return new static($value);
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

    public function equals(Enum $other): bool
    {
        return $other instanceof $this && $other->value() === $this->value();
    }

    public function assertEquals(Enum $other)
    {
        if (!$this->equals($other)) {
            throw new UnexpectedValueException(sprintf(
                'Failed to assert %s equals %s',
                $this->value(),
                $other->value()
            ));
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
     * @return Enum
     */
    public static function __callStatic($name, $arguments)
    {
        return new static(constant(static::class . '::' . $name));
    }
}
