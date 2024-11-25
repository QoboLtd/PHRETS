<?php
namespace PHRETS\Test;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use PHRETS\Arr;

class ArrTest extends TestCase
{
    public function testFirst(): void
    {
        $this->assertNull(Arr::first([]));
        $this->assertSame('VAL', Arr::first(['first' => 'VAL', 'second' => 'VAL2']));
        $this->assertSame('VAL', Arr::first(['VAL', 'VAL2']));
    }

    public function testLast(): void
    {
        $this->assertNull(Arr::last([]));
        $this->assertSame('VAL2', Arr::last(['first' => 'VAL', 'second' => 'VAL2']));
        $this->assertSame('VAL2', Arr::last(['VAL', 'VAL2']));
    }

    #[DataProvider('provideGet')]
    public function testGet(array $array, string $key, mixed $expected): void
    {
        $this->assertSame($expected, Arr::get($array, $key));
    }

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
