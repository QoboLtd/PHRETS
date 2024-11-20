<?php

class GetObjectIntegrationTest extends BaseIntegration
{
    /** @test */
    public function itFetchesObjects()
    {
        $objects = $this->session->GetObject('Property', 'Photo', '14-52', '*', 0);
        $this->assertTrue($objects instanceof \Illuminate\Support\Collection);
        $this->assertSame(22, $objects->count());
    }

    /** @test */
    public function itFetchesPrimaryObject()
    {
        $objects = $this->session->GetObject('Property', 'Photo', '00-1669', 0, 1);
        $this->assertTrue($objects instanceof \Illuminate\Support\Collection);
        $this->assertSame(1, $objects->count());

        $primary = $objects->first();

        $object = $this->session->GetPreferredObject('Property', 'Photo', '00-1669', 1);
        $this->assertTrue($object instanceof \PHRETS\Models\BaseObject);
        $this->assertEquals($primary, $object);
    }

    /** @test **/
    public function itSeesPrimaryAsPreferred()
    {
        $object = $this->session->GetPreferredObject('Property', 'Photo', '00-1669', 1);
        $this->assertTrue($object->isPreferred());
    }

    /** @test */
    public function itSeesLocationsDespiteXmlBeingReturned()
    {
        $object = $this->session->GetObject('Property', 'Photo', 'URLS-WITH-XML', '*', 1);

        $this->assertCount(1, $object);
        /** @var \PHRETS\Models\BaseObject $first */
        $first = $object->first();
        $this->assertFalse($first->isError());
        $this->assertSame('http://someurl', $first->getLocation());
    }
}
