<?php

class GetMetadataIntegrationTest extends BaseIntegration
{
    /**
     * System.
     */

    /** @test **/
    public function itGetsSystemData()
    {
        $system = $this->session->GetSystemMetadata();
        $this->assertTrue($system instanceof \PHRETS\Models\Metadata\System);
    }

    /** @test **/
    public function itGetsSystemDataFor15()
    {
        $config = new \PHRETS\Configuration();
        $config->setLoginUrl('http://retsgw.flexmls.com/rets2_1/Login')
                ->setUsername(getenv('PHRETS_TESTING_USERNAME'))
                ->setPassword(getenv('PHRETS_TESTING_PASSWORD'))
                ->setRetsVersion('1.5');

        $session = new \PHRETS\Session($config);
        $session->Login();

        $system = $session->GetSystemMetadata();
        $this->assertTrue($system instanceof \PHRETS\Models\Metadata\System);
        $this->assertSame('demomls', $system->getSystemID());
    }

    /** @test **/
    public function itMakesAGoodUrl()
    {
        $this->session->GetSystemMetadata();
        $this->assertSame(
            'http://retsgw.flexmls.com:80/rets2_1/GetMetadata?Type=METADATA-SYSTEM&ID=0&Format=STANDARD-XML',
            $this->session->getLastRequestURL()
        );
    }

    /** @test **/
    public function itSeesSomeAttributes()
    {
        $system = $this->session->GetSystemMetadata();
        $this->assertSame('demomls', $system->getSystemID());
        $this->assertSame('-05:00', $system->getTimeZoneOffset());
    }

    /** @test **/
    public function itGetsRelatedResources()
    {
        $system = $this->session->GetSystemMetadata()->getResources();
        $resources = $this->session->GetResourcesMetadata();
        $this->assertEquals($system, $resources);
    }

    /**
     * Resources.
     */

    /** @test **/
    public function itGetsResourceData()
    {
        $resources = $this->session->GetResourcesMetadata();
        $this->assertArrayHasKey('Property', $resources);
        $resource = $resources['Property'];

        $this->assertTrue($resource instanceof \PHRETS\Models\Metadata\Resource);
        $this->assertSame('Property', $resource->getStandardName());
        $this->assertSame('7', $resource->getClassCount());
    }

    /** @test **/
    public function itGetsAllResourceData()
    {
        $resources = $this->session->GetResourcesMetadata();
        $this->assertCount(9, $resources);
        $this->assertSame('ActiveAgent', $this->first($resources)?->getResourceID());
        $this->assertSame('VirtualTour', $this->last($resources)?->getResourceID());
    }

    /** @test **/
    public function itGetsKeyedResourceData()
    {
        $resources = $this->session->GetResourcesMetadata();
        $this->assertArrayHasKey('Property', $resources);
        $this->assertInstanceOf(\PHRETS\Models\Metadata\Resource::class, $resources['Property']);
    }

    /**
     * @test
     * **/
    public function itErrorsWithBadResourceName()
    {
        $resources = $this->session->GetResourcesMetadata();
        $this->assertArrayNotHasKey('Bogus', $resources);
    }

    /** @test **/
    public function itGetsRelatedClasses()
    {
        $resources = $this->session->GetResourcesMetadata();
        $this->assertArrayHasKey('Property', $resources);

        $resource_classes = $resources['Property']->getClasses();
        $classes = $this->session->GetClassesMetadata('Property');
        $this->assertEquals($resource_classes, $classes);
    }

    /** @test **/
    public function itGetsRelatedObjectMetadata()
    {
        $resources = $this->session->GetResourcesMetadata();
        $this->assertArrayHasKey('Property', $resources);

        $object_types = $resources['Property']->getObject();
        $this->assertSame('Photo', reset($object_types)->getObjectType());
    }

    /**
     * Classes.
     */

    /** @test **/
    public function itGetsClassData()
    {
        $classes = $this->session->GetClassesMetadata('Property');
        $this->assertIsArray($classes);
        $this->assertSame(7, count($classes));
        $this->assertSame('A', reset($classes)->getClassName());
    }

    /** @test **/
    public function itGetsRelatedTableData()
    {
        $classes = $this->session->GetClassesMetadata('Property');
        $this->assertIsArray($classes);
        $firstClass = $this->first($classes);

        $this->assertSame('LIST_0', $this->first($firstClass->getTable())->getSystemName());
    }

    /** @test **/
    public function itGetsKeyedClassMetadata()
    {
        $classes = $this->session->GetClassesMetadata('Property');
        $this->assertInstanceOf(\PHRETS\Models\Metadata\ResourceClass::class, $classes['A']);
    }

    /**
     * Table.
     */

    /** @test **/
    public function itGetsTableData()
    {
        $fields = $this->session->GetTableMetadata('Property', 'A');
        $this->assertTrue(count($fields) > 100, 'Verify that a lot of fields came back');
        $this->assertSame('LIST_0', $this->first($fields)?->getSystemName());
    }

    /** @test **/
    public function itSeesTableAttributes()
    {
        $fields = $this->session->GetTableMetadata('Property', 'A');
        $this->assertSame('Property', $this->first($fields)?->getResource());
        $this->assertSame('A', $this->last($fields)?->getClass());
    }

    /** @test **/
    public function itSeesFieldsByKey()
    {
        $fields = $this->session->GetTableMetadata('Property', 'A');
        $this->assertSame('Listing ID', $fields['LIST_105']->getLongName());
    }

    /** @test **/
    public function itSeesFieldsByStandardKey()
    {
        $fields = $this->session->GetTableMetadata('Property', 'A', 'StandardName');
        $this->assertSame('Listing ID', $fields['ListingID']->getLongName());
    }

    /** @test **/
    public function itGetsObjectMetadata()
    {
        $object_types = $this->session->GetObjectMetadata('Property');
        $this->assertTrue(count($object_types) > 4, 'Verify that a few came back');
        $this->assertSame('Photo', $this->first($object_types)?->getObjectType());
        $this->assertSame('LIST_133', $this->first($object_types)->getObjectCount());
    }

    /** @test **/
    public function itGetsKeyedObjectMetadata()
    {
        $object_types = $this->session->GetObjectMetadata('Property');
        $this->assertInstanceOf('\PHRETS\Models\Metadata\BaseObject', $object_types['Photo']);
    }

    /**
     * Lookups.
     */

    /** @test **/
    public function itGetsLookupValues()
    {
        $values = $this->session->GetLookupValues('Property', '20000426151013376279000000');
        $first = $this->first($values);

        $this->assertSame('Lake/Other', $first->getLongValue());
        $this->assertSame('5PSUX49PM1Q', $first->getValue());
    }

    /** @test **/
    public function itGetsRelatedLookupValues()
    {
        $fields = $this->session->GetTableMetadata('Property', 'A');

        $quick_way = $fields['LIST_9']->getLookupValues();
        $manual_way = $this->session->GetLookupValues('Property', '20000426151013376279000000');

        $this->assertEquals($this->first($quick_way), $this->first($manual_way));
    }

    /**
     * @template T
     * @param array<int|string,T> $values
     * @return T|null
     */
    private function first(array $values): mixed
    {
        $key = array_key_first($values);
        if ($key === null) {
            return null;
        }

        return $values[$key];
    }

     /**
     * @template T
     * @param array<int|string,T> $values
     * @return T|null
     */
    private function last(array $values): mixed
    {
        $key = array_key_last($values);
        if ($key === null) {
            return null;
        }

        return $values[$key];
    }

    /** @test **/
    public function itRecoversFromBadLookuptypeTag()
    {
        $config = new \PHRETS\Configuration();
        $config->setLoginUrl('http://retsgw.flexmls.com/lookup/rets2_1/Login')
                ->setUsername(getenv('PHRETS_TESTING_USERNAME'))
                ->setPassword(getenv('PHRETS_TESTING_PASSWORD'))
                ->setRetsVersion('1.5');

        $session = new \PHRETS\Session($config);
        $session->Login();

        $values = $session->GetLookupValues('Property', '20000426151013376279000000');
        $this->assertCount(6, $values);
    }

    /** @test **/
    public function itHandlesIncompleteObjectMetadataCorrectly()
    {
        $config = new \PHRETS\Configuration();
        $config->setLoginUrl('http://retsgw.flexmls.com/rets2_1/Login')
            ->setUsername(getenv('PHRETS_TESTING_USERNAME'))
            ->setPassword(getenv('PHRETS_TESTING_PASSWORD'))
            ->setRetsVersion('1.5');

        $session = new \PHRETS\Session($config);
        $session->Login();

        $values = $session->GetObjectMetadata('PropertyPowerProduction');
        $this->assertCount(0, $values);
    }
}
