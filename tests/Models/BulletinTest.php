<?php
namespace PHRETS\Test\Models;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PHRETS\Models\Bulletin;

class BulletinTest extends TestCase
{
    #[Test]
    public function itHolds(): void
    {
        $this->assertSame('Hello World', (new Bulletin())->setBody('Hello World')->getBody());
    }

    #[Test]
    public function itTurnsItselfIntoAString(): void
    {
        $this->assertSame('Hello World', (string) (new Bulletin())->setBody('Hello World'));
    }

    public function testDetailsAreMadeAvailable(): void
    {
        $bulletin = new Bulletin(['Test' => 'Value']);
        $this->assertSame('Value', $bulletin->getDetail('Test'));
    }
}
