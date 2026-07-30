<?php
namespace PHRETS\Test\Versions;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PHRETS\Enums\RETSVersion;

class RETSVersionTest extends TestCase
{
    #[Test]
    public function itLoads(): void
    {
        self::assertSame('1.7.2', RETSVersion::VERSION_1_7_2->value);
    }

    #[Test]
    public function itMakesTheHeader(): void
    {
        self::assertSame('RETS/1.7.2', RETSVersion::VERSION_1_7_2->asHeader());
    }

    #[Test]
    public function itIs15(): void
    {
        self::assertTrue(RETSVersion::VERSION_1_5->isAtLeast(RETSVersion::VERSION_1_5));
    }

    #[Test]
    public function itIs17(): void
    {
        $v = RETSVersion::VERSION_1_7;

        self::assertTrue($v->isAtLeast(RETSVersion::VERSION_1_7));
        self::assertFalse($v->isAtLeast(RETSVersion::VERSION_1_7_2));
    }

    #[Test]
    public function itIs172(): void
    {
        $v = RETSVersion::VERSION_1_7_2;

        self::assertTrue($v->isAtLeast(RETSVersion::VERSION_1_7));
        self::assertTrue($v->isAtLeast(RETSVersion::VERSION_1_7_2));
        self::assertFalse($v->isAtLeast(RETSVersion::VERSION_1_8));
    }

    #[Test]
    public function itIs18(): void
    {
        $v = RETSVersion::VERSION_1_8;

        self::assertTrue($v->isAtLeast(RETSVersion::VERSION_1_5));
        self::assertTrue($v->isAtLeast(RETSVersion::VERSION_1_7));
        self::assertTrue($v->isAtLeast(RETSVersion::VERSION_1_7_2));
        self::assertTrue($v->isAtLeast(RETSVersion::VERSION_1_8));
    }
}
