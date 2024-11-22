<?php

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PHRETS\Models\BaseObject;

class ObjectTest extends TestCase
{
    #[Test]
    public function itHolds()
    {
        $o = new BaseObject();
        $o->setContent('Test Content');

        $this->assertSame('Test Content', $o->getContent());
    }

    #[Test]
    public function itReturnsASize()
    {
        $o = new BaseObject();
        $o->setContent('Hello');

        $this->assertSame(5, $o->getSize());
    }

    #[Test]
    public function itMakesFromHeaders()
    {
        $headers = [
            'Content-Type' => 'image/jpeg',
            'Content-ID' => '12345678',
            'Object-ID' => '1',
            'Location' => 'http://blah',
            'Content-Description' => 'Main description',
            'Content-Sub-Description' => 'Sub description',
            'MIME-Version' => 'Mime Version',
        ];

        $o = new BaseObject();
        foreach ($headers as $k => $v) {
            $o->setFromHeader($k, $v);
        }

        $this->assertSame('image/jpeg', $o->getContentType());
        $this->assertSame('12345678', $o->getContentId());
        $this->assertSame('1', $o->getObjectId());
        $this->assertSame('http://blah', $o->getLocation());
        $this->assertSame('Main description', $o->getContentDescription());
        $this->assertSame('Sub description', $o->getContentSubDescription());
        $this->assertSame('Mime Version', $o->getMimeVersion());
    }

    #[Test]
    public function itMarksPreferredObjects()
    {
        $o = new BaseObject();
        $this->assertFalse($o->isPreferred());
        $o->setPreferred(1);
        $this->assertTrue($o->isPreferred());
        $this->assertSame(1, $o->getPreferred());
    }

    #[Test]
    public function itMarksErrors()
    {
        $e = new \PHRETS\Models\RETSError();
        $e->setCode('1234');
        $e->setMessage('Test Error Message');

        $o = new BaseObject();
        $this->assertFalse($o->isError());
        $o->setError($e);
        $this->assertTrue($o->isError());
        $this->assertSame('1234', $o->getError()?->getCode());
        $this->assertSame('Test Error Message', $o->getError()->getMessage());
    }
}
