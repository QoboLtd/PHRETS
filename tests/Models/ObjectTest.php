<?php
namespace PHRETS\Test\Models;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PHRETS\Models\BaseObject;

class ObjectTest extends TestCase
{
    #[Test]
    public function itHolds(): void
    {
        $o = new BaseObject();
        $o->setContent('Test Content');

        self::assertSame('Test Content', $o->getContent());
    }

    #[Test]
    public function itReturnsASize(): void
    {
        $o = new BaseObject();
        $o->setContent('Hello');

        self::assertSame(5, $o->getSize());
    }

    #[Test]
    public function itMakesFromHeaders(): void
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

        self::assertSame('image/jpeg', $o->getContentType());
        self::assertSame('12345678', $o->getContentId());
        self::assertSame('1', $o->getObjectId());
        self::assertSame('http://blah', $o->getLocation());
        self::assertSame('Main description', $o->getContentDescription());
        self::assertSame('Sub description', $o->getContentSubDescription());
        self::assertSame('Mime Version', $o->getMimeVersion());
    }

    #[Test]
    public function itMarksPreferredObjects(): void
    {
        $o = new BaseObject();
        self::assertFalse($o->isPreferred());
        $o->setPreferred(1);
        self::assertTrue($o->isPreferred());
        self::assertSame(1, $o->getPreferred());
    }

    #[Test]
    public function itMarksErrors(): void
    {
        $e = new \PHRETS\Models\RETSError();
        $e->setCode('1234');
        $e->setMessage('Test Error Message');

        $o = new BaseObject();
        self::assertFalse($o->isError());
        $o->setError($e);
        self::assertTrue($o->isError());
        self::assertSame('1234', $o->getError()?->getCode());
        self::assertSame('Test Error Message', $o->getError()->getMessage());
    }
}
