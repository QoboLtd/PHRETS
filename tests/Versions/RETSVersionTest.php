<?php
namespace PHRETS\Test\Versions;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PHRETS\Versions\RETSVersion;

class RETSVersionTest extends TestCase
{
    #[Test]
    public function itLoads(): void
    {
        $this->assertSame('1.7.2', (new RETSVersion())->setVersion('1.7.2')->getVersion());
    }

    #[Test]
    public function itCleans(): void
    {
        $this->assertSame('1.7.2', (new RETSVersion())->setVersion('RETS/1.7.2')->getVersion());
    }

    #[Test]
    public function itMakesTheHeader(): void
    {
        $this->assertSame('RETS/1.7.2', (new RETSVersion())->setVersion('1.7.2')->asHeader());
    }

    #[Test]
    public function itIs15(): void
    {
        $v = new RETSVersion();
        $v->setVersion('RETS/1.5');

        $this->assertTrue($v->is1_5());
        $this->assertTrue($v->isAtLeast1_5());
    }

    #[Test]
    public function itIs17(): void
    {
        $v = new RETSVersion();
        $v->setVersion('RETS/1.7');

        $this->assertTrue($v->is1_7());
        $this->assertFalse($v->is1_5());
        $this->assertFalse($v->is1_7_2());
        $this->assertTrue($v->isAtLeast1_7());
        $this->assertFalse($v->isAtLeast1_7_2());
    }

    #[Test]
    public function itIs172(): void
    {
        $v = new RETSVersion();
        $v->setVersion('RETS/1.7.2');

        $this->assertFalse($v->is1_7());
        $this->assertFalse($v->is1_5());
        $this->assertTrue($v->is1_7_2());
        $this->assertTrue($v->isAtLeast1_7());
        $this->assertTrue($v->isAtLeast1_7_2());
        $this->assertFalse($v->isAtLeast1_8());
    }

    #[Test]
    public function itIs18(): void
    {
        $v = new RETSVersion();
        $v->setVersion('RETS/1.8');

        $this->assertTrue($v->is1_8());
        $this->assertFalse($v->is1_7());
        $this->assertFalse($v->is1_5());
        $this->assertFalse($v->is1_7_2());
        $this->assertTrue($v->isAtLeast1_7());
        $this->assertTrue($v->isAtLeast1_7_2());
        $this->assertTrue($v->isAtLeast1_8());
    }

    #[Test]
    public function itCompares(): void
    {
        $v = new RETSVersion();
        $v->setVersion('RETS/1.8');

        $this->assertTrue($v->isAtLeast('1.5'));
        $this->assertTrue($v->isAtLeast('1.7'));
        $this->assertTrue($v->isAtLeast('1.7.2'));
    }

    #[Test]
    public function itFailsBadVersions(): void
    {
        $this->expectException(\PHRETS\Exceptions\InvalidRETSVersion::class);
        $v = new RETSVersion();
        $v->setVersion('2.0');
    }

    #[Test]
    public function itConvertsToString(): void
    {
        $v = new RETSVersion();
        $v->setVersion('1.7.2');

        $this->assertSame('RETS/1.7.2', (string) $v);
    }
}
