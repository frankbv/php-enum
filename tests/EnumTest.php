<?php

namespace Frank\Test;

use Frank\Enum;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

class EnumTest extends TestCase
{
    public function testValidConstruction()
    {
        $this->assertEquals(2, (new _EnumTest(2))->value());
    }

    public function testInvalidConstruction()
    {
        $this->expectException(InvalidArgumentException::class);
        new _EnumTest(10);
    }

    public function testGetAll()
    {
        $all = _EnumTest::all();
        $this->assertTrue($all['HELLO']->is(5));
        $this->assertTrue($all['NOT_TRUE']->is(false));
    }

    public function testAssertEquals()
    {
        (new _EnumTest(2))->assertEquals(_EnumTest::BAR());
        $this->assertTrue(true);
    }

    public function testAssertEqualsWhenInstanceOf()
    {
        (new _EnumTest(2))->assertEquals(
            new class(2) extends _EnumTest {
                const BAZ = 'baz';
            }
        );

        $this->assertTrue(true);
    }

    /**
     * @param mixed $other
     * @dataProvider notEqualsProvider
     */
    public function testAssertEqualsFailsWhenNotEqual($other)
    {
        $this->expectException(UnexpectedValueException::class);
        (new _EnumTest(2))->assertEquals($other);
    }

    public function notEqualsProvider()
    {
        return [
            [_EnumTest::FOO()],
            [
                new class('baz') extends _EnumTest {
                    const BAZ = 'baz';
                }
            ],
        ];
    }

    /**
     * @param mixed $other
     * @dataProvider notInstanceOfProvider
     */
    public function testAssertEqualsFailsWhenNotInstanceOf($other)
    {
        $this->expectException(InvalidArgumentException::class);
        (new _EnumTest(2))->assertEquals($other);
    }

    public function notInstanceOfProvider()
    {
        return [
            [null],
            [false],
            [1],
            [3.14],
            ['string'],
            [new class() {}],
            [
                new class(1) extends Enum
                {
                    const FOO = 1;
                }
            ],
        ];
    }

    /**
     * @param $value
     * @dataProvider validValues
     */
    public function testForValidValues($value)
    {
        $this->assertTrue(_EnumTest::isValidValue($value));
    }

    public function validValues(): array
    {
        return [[1], [2], [5], [7], ['placed'], ['yolo'], [false]];
    }

    /**
     * @param $value
     * @dataProvider invalidValues
     */
    public function testForInvalidValues($value)
    {
        $this->assertFalse(_EnumTest::isValidValue($value));
    }

    public function invalidValues(): array
    {
        return [[-1], [0], [4], [10], ['ape'], ['BANANAS'], [true], [[1, 2, 3]]];
    }

    public function testItGivesRightConstants()
    {
        $expected = [
            'FOO' => 1,
            'BAR' => 2,
            'HELLO' => 5,
            'BYE' => 7,
            'PLACED' => 'placed',
            'YOLO' => 'yolo',
            'NOT_TRUE' => false,
        ];

        $this->assertEquals($expected, _EnumTest::getConstants());
    }

    public function testToString()
    {
        $this->assertEquals('1', _EnumTest::FOO()->__toString());
        $this->assertEquals((string)false, _EnumTest::NOT_TRUE()->__toString());
    }
}

/**
 * @method static _EnumTest FOO()
 * @method static _EnumTest BAR()
 * @method static _EnumTest NOT_TRUE()
 */
class _EnumTest extends Enum
{
    const FOO = 1;
    const BAR = 2;
    const HELLO = 5;
    const BYE = 7;
    const PLACED = 'placed';
    const YOLO = 'yolo';
    const NOT_TRUE = false;
}
