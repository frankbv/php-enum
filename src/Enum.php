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
abstract class Enum implements \Stringable
{
    /**
     * Cached array of arrays with constants for each parent class
     * indexed by classname
     * @var array<string, array<string, mixed>>
     */
    private static array $constants = [];
    /**
     * Cache of enums retrieved through static calls
     *
     * @var array<string, array<Enum>>
     */
    private static array $cache = [];

    /**
     * @var mixed the internal value
     */
    private $value;

    /**
     * @param mixed $value
     */
    final private function __construct($value)
    {
        if (!static::isValidValue($value)) {
            throw new InvalidArgumentException(sprintf('%s is not a valid value for %s', $value, static::class));
        }
        $this->value = $value;
    }

    final private function __clone()
    {
    }

    /**
     * @return array<string, mixed> array of constants name => value
     */
    public static function getConstants(): array
    {
        $classname = static::class;
        if (!isset(self::$constants[$classname])) {
            $reflections = (new ReflectionClass($classname))->getReflectionConstants();

            self::$constants[$classname] = [];
            foreach ($reflections as $reflection) {
                if ($reflection->isPublic()) {
                    self::$constants[$classname][$reflection->getName()] = $reflection->getValue();
                }
            }
        }
        return self::$constants[$classname];
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public static function isValidValue($value): bool
    {
        return \in_array($value, self::getConstants(), true);
    }

    /**
     * @return static[]
     */
    public static function all(): array
    {
        return \array_map(function ($value) {
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
     * @param mixed[] $values
     * @return bool
     */
    public function isAny(array $values): bool
    {
        foreach ($values as $value) {
            if ($this->is($value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param mixed $other
     * @return bool
     */
    public function equals($other): bool
    {
        return $other instanceof $this && $this->is($other->value());
    }

    /**
     * @param mixed[] $others
     * @return bool
     */
    public function equalsAny(array $others): bool
    {
        foreach ($others as $other) {
            if ($this->equals($other)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param mixed $other
     * @throws UnexpectedValueException
     * @throws InvalidArgumentException
     */
    public function assertEquals($other): void
    {
        if (!$this->equals($other)) {
            if ($other instanceof $this) {
                throw new UnexpectedValueException(\sprintf(
                    'Failed to assert %s equals %s',
                    $this->value(),
                    $other->value()
                ));
            } else {
                throw new InvalidArgumentException(\sprintf(
                    'Cannot compare %s to %s',
                    \is_object($other) ? \get_class($other) : \gettype($other),
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
     * @param mixed[] $values
     * @return static[]
     */
    public final static function ofList(array $values): array
    {
        return \array_map(static fn($value) => self::of($value), $values);
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
        if (!isset($constants[$name]) && !array_key_exists($name, $constants)) {
            throw new InvalidArgumentException(sprintf('%s does not exist in %s', $name, static::class));
        }

        return static::of($constants[$name]);
    }
}
