<?php

use PHPUnit\Framework\TestCase;
use PHRETS\Versions\RETSVersion;

class RETSVersionTest extends TestCase
{
    /** @test **/
    public function itLoads()
    {
        $this->assertSame('1.7.2', (new RETSVersion())->setVersion('1.7.2')->getVersion());
    }

    /** @test **/
    public function itCleans()
    {
        $this->assertSame('1.7.2', (new RETSVersion())->setVersion('RETS/1.7.2')->getVersion());
    }

    /** @test **/
    public function itMakesTheHeader()
    {
        $this->assertSame('RETS/1.7.2', (new RETSVersion())->setVersion('1.7.2')->asHeader());
    }

    /** @test **/
    public function itIs15()
    {
        $v = new RETSVersion();
        $v->setVersion('RETS/1.5');

        $this->assertTrue($v->is1_5());
        $this->assertTrue($v->isAtLeast1_5());
    }

    /** @test **/
    public function itIs17()
    {
        $v = new RETSVersion();
        $v->setVersion('RETS/1.7');

        $this->assertTrue($v->is1_7());
        $this->assertFalse($v->is1_5());
        $this->assertFalse($v->is1_7_2());
        $this->assertTrue($v->isAtLeast1_7());
        $this->assertFalse($v->isAtLeast1_7_2());
    }

    /** @test **/
    public function itIs172()
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

    /** @test **/
    public function itIs18()
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

    /** @test **/
    public function itCompares()
    {
        $v = new RETSVersion();
        $v->setVersion('RETS/1.8');

        $this->assertTrue($v->isAtLeast('1.5'));
        $this->assertTrue($v->isAtLeast('1.7'));
        $this->assertTrue($v->isAtLeast('1.7.2'));
    }

    /**
     * @test
     * **/
    public function itFailsBadVersions()
    {
        $this->expectException(\PHRETS\Exceptions\InvalidRETSVersion::class);
        $v = new RETSVersion();
        $v->setVersion('2.0');
    }

    /** @test **/
    public function itConvertsToString()
    {
        $v = new RETSVersion();
        $v->setVersion('1.7.2');

        $this->assertSame('RETS/1.7.2', (string) $v);
    }
}
