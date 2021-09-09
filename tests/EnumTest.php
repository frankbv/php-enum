<?php

namespace Frank\Test;

use Frank\Enum;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

class EnumTest extends TestCase
{
    public function testValidConstruction(): void
    {
        $this->assertEquals(2, TestEnum::of(2)->value());
    }

    public function testInvalidConstruction(): void
    {
        $this->expectException(InvalidArgumentException::class);
        TestEnum::of(10);
    }

    public function testGetAll(): void
    {
        $all = TestEnum::all();
        $this->assertTrue($all['HELLO']->is(5));
        $this->assertTrue($all['NOT_TRUE']->is(false));
    }

    public function testAssertEquals(): void
    {
        TestEnum::of(2)->assertEquals(TestEnum::BAR());
        $this->assertTrue(true);
    }

    public function testAssertEqualsWhenInstanceOf(): void
    {
        TestEnum::of(2)->assertEquals(ChildTestEnum::of(2));

        $this->assertTrue(true);
    }

    /**
     * @param mixed $other
     * @dataProvider notEqualsProvider
     */
    public function testAssertEqualsFailsWhenNotEqual($other): void
    {
        $this->expectException(UnexpectedValueException::class);
        TestEnum::of(2)->assertEquals($other);
    }

    public function notEqualsProvider(): iterable
    {
        return [
            [TestEnum::FOO()],
            [ChildTestEnum::of('baz')],
        ];
    }

    /**
     * @param mixed $other
     * @dataProvider notInstanceOfProvider
     */
    public function testAssertEqualsFailsWhenNotInstanceOf($other): void
    {
        $this->expectException(InvalidArgumentException::class);
        TestEnum::of(2)->assertEquals($other);
    }

    public function notInstanceOfProvider(): iterable
    {
        return [
            [null],
            [false],
            [1],
            [3.14],
            ['string'],
            [new class() {}],
            [AnotherTestEnum::of(1)],
        ];
    }

    /**
     * @param $value
     * @dataProvider validValues
     */
    public function testForValidValues($value): void
    {
        $this->assertTrue(TestEnum::isValidValue($value));
    }

    public function validValues(): array
    {
        return [[1], [2], [5], [7], ['placed'], ['yolo'], [false]];
    }

    /**
     * @param $value
     * @dataProvider invalidValuesProvider
     */
    public function testForInvalidValues($value): void
    {
        $this->assertFalse(TestEnum::isValidValue($value));
    }

    public function invalidValuesProvider(): array
    {
        return [[-1], [0], [4], [10], ['ape'], ['BANANAS'], [true], [[1, 2, 3]]];
    }

    public function testItGivesRightConstants(): void
    {
        $expected = [
            'FOO' => 1,
            'BAR' => 2,
            'HELLO' => 5,
            'BYE' => 7,
            'PLACED' => 'placed',
            'YOLO' => 'yolo',
            'NOT_TRUE' => false,
            'NONE' => null,
        ];

        $this->assertEquals($expected, TestEnum::getConstants());
    }

    public function testToString(): void
    {
        $this->assertEquals('1', TestEnum::FOO()->__toString());
        $this->assertEquals((string)false, TestEnum::NOT_TRUE()->__toString());
    }

    public function testStaticCallsProduceTheSameObject(): void
    {
        $this->assertSame(TestEnum::FOO(), TestEnum::FOO());
    }

    public function testOfProduceTheSameObjectAsStaticCalls(): void
    {
        $this->assertSame(TestEnum::of(TestEnum::FOO), TestEnum::FOO());
    }

    public function testInvalidStaticCallsProduceAnException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('DOESNOTEXIST does not exist in ' . TestEnum::class);

        TestEnum::DOESNOTEXIST();
    }

    public function testNullValueDoesNotProduceAnException(): void
    {
        $this->assertNull(TestEnum::NONE()->value());
    }

    public function testMemoryUsage(): void
    {
        $iterations = 1000;
        $list = range(0, $iterations);

        $start = memory_get_usage();
        foreach ($list as $ii) {
            $list[$ii] = TestEnum::FOO();
        }
        $end = memory_get_usage();

        $this->assertEquals($start, $end);
    }

    public function testIsAny(): void
    {
        $this->assertTrue(TestEnum::FOO()->isAny(['dsdf', null, '1', TestEnum::FOO, true]));
        $this->assertFalse(TestEnum::FOO()->isAny(['dsdf', null, '1', true]));
    }

    public function testEqualsAny(): void
    {
        $this->assertTrue(TestEnum::FOO()->equalsAny(TestEnum::all()));
        $this->assertFalse(TestEnum::FOO()->equalsAny([TestEnum::NONE(), TestEnum::BAR(), new class() {}]));
    }

    public function testOfList()
    {
        $this->assertEquals([TestEnum::FOO(), TestEnum::BAR()], TestEnum::ofList([TestEnum::FOO, TestEnum::BAR]));
    }

    public function testDontAllowEnumCreationWithPrivateConst()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('private is not a valid value for ' . TestEnum::class);
        TestEnum::of('private');
    }

    public function testDontExposePrivateConst()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('I_AM_PRIVATE does not exist in ' . TestEnum::class);
        TestEnum::I_AM_PRIVATE();
    }

    public function testDontAllowEnumCreationWithProtectedConst()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('protected is not a valid value for ' . TestEnum::class);
        TestEnum::of('protected');
    }

    public function testDontExposeProtectedConst()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('I_AM_PROTECTED does not exist in ' . TestEnum::class);
        TestEnum::I_AM_PROTECTED();
    }

    public function testCloningIsForbidden()
    {
        $this->expectException(\Error::class);
        clone TestEnum::BAR();
    }
}

/**
 * @method static static FOO()
 * @method static static BAR()
 * @method static static NOT_TRUE()
 * @method static static NONE()
 */
class TestEnum extends Enum
{
    const FOO = 1;
    const BAR = 2;
    const HELLO = 5;
    const BYE = 7;
    const PLACED = 'placed';
    const YOLO = 'yolo';
    const NOT_TRUE = false;
    const NONE = null;

    private const I_AM_PRIVATE = 'private';
    protected const I_AM_PROTECTED = 'protected';
}

class ChildTestEnum extends TestEnum
{
    const BAZ = 'baz';
}

class AnotherTestEnum extends Enum
{
    const FOO = 1;
}
