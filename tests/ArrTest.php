<?php
namespace PHRETS\Test;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use PHRETS\Arr;

class ArrTest extends TestCase
{
    public function testFirst(): void
    {
        self::assertNull(Arr::first([]));
        self::assertSame('VAL', Arr::first(['first' => 'VAL', 'second' => 'VAL2']));
        self::assertSame('VAL', Arr::first(['VAL', 'VAL2']));
    }

    public function testLast(): void
    {
        self::assertNull(Arr::last([]));
        self::assertSame('VAL2', Arr::last(['first' => 'VAL', 'second' => 'VAL2']));
        self::assertSame('VAL2', Arr::last(['VAL', 'VAL2']));
    }

    /**
     * @param list<array{0:array<int|string,mixed>,1:string,2:mixed}> $array
     */
    #[DataProvider('provideGet')]
    public function testGet(array $array, string $key, mixed $expected): void
    {
        self::assertSame($expected, Arr::get($array, $key));
    }

    /**
     * @return list<array{0:array<int|string,mixed>,1:string,2:mixed}>
     */
    public static function provideGet(): array
    {
        return [
            [
                [],
                'key',
                null
            ],
            [
                ['key' => null],
                'key',
                null
            ],
            [
                ['key' => 'VALUE'],
                'key',
                'VALUE',
            ],
            [
                ['one.two' => 'VALUE'],
                'one.two',
                'VALUE',
            ],
            [
                ['one' => ['two' => 'VALUE']],
                'one.two',
                'VALUE',
            ]
        ];
    }
}
