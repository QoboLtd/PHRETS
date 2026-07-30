<?php
namespace PHRETS\Test\Integration;

use PHPUnit\Framework\Attributes\Test;

class GetObjectIntegrationTest extends BaseIntegration
{
    #[Test]
    public function itFetchesObjects(): void
    {
        $objects = $this->session->GetObject('Property', 'Photo', '14-52', '*', 0);
        self::assertSame(22, count($objects));
    }

    #[Test]
    public function itFetchesPrimaryObject(): void
    {
        $objects = $this->session->GetObject('Property', 'Photo', '00-1669', 0, 1);
        self::assertSame(1, count($objects));

        $primary = $objects[0];

        $object = $this->session->GetPreferredObject('Property', 'Photo', '00-1669', 1);
        self::assertTrue($object instanceof \PHRETS\Models\BaseObject);
        self::assertEquals($primary, $object);
    }

    #[Test]
    public function itSeesPrimaryAsPreferred(): void
    {
        $object = $this->session->GetPreferredObject('Property', 'Photo', '00-1669', 1);
        self::assertTrue($object->isPreferred());
    }

    #[Test]
    public function itSeesLocationsDespiteXmlBeingReturned(): void
    {
        $object = $this->session->GetObject('Property', 'Photo', 'URLS-WITH-XML', '*', 1);

        self::assertCount(1, $object);
        $first = $object[0];
        self::assertFalse($first->isError());
        self::assertSame('http://someurl', $first->getLocation());
    }
}
