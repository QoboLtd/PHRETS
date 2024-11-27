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
        $this->assertSame('1.7.2', RETSVersion::VERSION_1_7_2->value);
    }

    #[Test]
    public function itMakesTheHeader(): void
    {
        $this->assertSame('RETS/1.7.2', RETSVersion::VERSION_1_7_2->asHeader());
    }

    #[Test]
    public function itIs15(): void
    {
        $this->assertTrue(RETSVersion::VERSION_1_5->isAtLeast(RETSVersion::VERSION_1_5));
    }

    #[Test]
    public function itIs17(): void
    {
        $v = RETSVersion::VERSION_1_7;

        $this->assertTrue($v->isAtLeast(RETSVersion::VERSION_1_7));
        $this->assertFalse($v->isAtLeast(RETSVersion::VERSION_1_7_2));
    }

    #[Test]
    public function itIs172(): void
    {
        $v = RETSVersion::VERSION_1_7_2;

        $this->assertTrue($v->isAtLeast(RETSVersion::VERSION_1_7));
        $this->assertTrue($v->isAtLeast(RETSVersion::VERSION_1_7_2));
        $this->assertFalse($v->isAtLeast(RETSVersion::VERSION_1_8));
    }

    #[Test]
    public function itIs18(): void
    {
        $v = RETSVersion::VERSION_1_8;

        $this->assertTrue($v->isAtLeast(RETSVersion::VERSION_1_5));
        $this->assertTrue($v->isAtLeast(RETSVersion::VERSION_1_7));
        $this->assertTrue($v->isAtLeast(RETSVersion::VERSION_1_7_2));
        $this->assertTrue($v->isAtLeast(RETSVersion::VERSION_1_8));
    }
}
