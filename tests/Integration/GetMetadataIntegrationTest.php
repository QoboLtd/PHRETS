<?php

use PHPUnit\Framework\Attributes\Test;
use PHRETS\Arr;

class GetMetadataIntegrationTest extends BaseIntegration
{
    /**
     * System.
     */

    #[Test]
    public function itGetsSystemData()
    {
        $system = $this->session->GetSystemMetadata();
        $this->assertTrue($system instanceof \PHRETS\Models\Metadata\System);
    }

    #[Test]
    public function itGetsSystemDataFor15()
    {
        $config = new \PHRETS\Configuration();
        $config->setLoginUrl('http://retsgw.flexmls.com/rets2_1/Login')
                ->setUsername(getenv('PHRETS_TESTING_USERNAME'))
                ->setPassword(getenv('PHRETS_TESTING_PASSWORD'))
                ->setRetsVersion('1.5');

        $session = $this->createSession($config);
        $session->Login();

        $system = $session->GetSystemMetadata();
        $this->assertTrue($system instanceof \PHRETS\Models\Metadata\System);
        $this->assertSame('demomls', $system->getSystemID());
    }

    #[Test]
    public function itMakesAGoodUrl()
    {
        $this->session->GetSystemMetadata();
        $this->assertSame(
            'http://retsgw.flexmls.com:80/rets2_1/GetMetadata?Type=METADATA-SYSTEM&ID=0&Format=STANDARD-XML',
            $this->session->getLastRequestURL()
        );
    }

    #[Test]
    public function itSeesSomeAttributes()
    {
        $system = $this->session->GetSystemMetadata();
        $this->assertSame('demomls', $system->getSystemID());
        $this->assertSame('-05:00', $system->getTimeZoneOffset());
    }

    #[Test]
    public function itGetsRelatedResources()
    {
        $system = $this->session->GetSystemMetadata()->getResources();
        $resources = $this->session->GetResourcesMetadata();
        $this->assertEquals($system, $resources);
    }

    /**
     * Resources.
     */

    #[Test]
    public function itGetsResourceData()
    {
        $resources = $this->session->GetResourcesMetadata();
        $this->assertArrayHasKey('Property', $resources);
        $resource = $resources['Property'];

        $this->assertTrue($resource instanceof \PHRETS\Models\Metadata\Resource);
        $this->assertSame('Property', $resource->getStandardName());
        $this->assertSame('7', $resource->getClassCount());
    }

    #[Test]
    public function itGetsAllResourceData()
    {
        $resources = $this->session->GetResourcesMetadata();
        $this->assertCount(9, $resources);
        $this->assertSame('ActiveAgent', Arr::first($resources)?->getResourceID());
        $this->assertSame('VirtualTour', Arr::last($resources)?->getResourceID());
    }

    #[Test]
    public function itGetsKeyedResourceData()
    {
        $resources = $this->session->GetResourcesMetadata();
        $this->assertArrayHasKey('Property', $resources);
        $this->assertInstanceOf(\PHRETS\Models\Metadata\Resource::class, $resources['Property']);
    }

    #[Test]
    public function itErrorsWithBadResourceName()
    {
        $resources = $this->session->GetResourcesMetadata();
        $this->assertArrayNotHasKey('Bogus', $resources);
    }

    #[Test]
    public function itGetsRelatedClasses()
    {
        $resources = $this->session->GetResourcesMetadata();
        $this->assertArrayHasKey('Property', $resources);

        $resource_classes = $resources['Property']->getClasses();
        $classes = $this->session->GetClassesMetadata('Property');
        $this->assertEquals($resource_classes, $classes);
    }

    #[Test]
    public function itGetsRelatedObjectMetadata()
    {
        $resources = $this->session->GetResourcesMetadata();
        $this->assertArrayHasKey('Property', $resources);

        $object_types = $resources['Property']->getObject();
        $this->assertSame('Photo', Arr::first($object_types)?->getObjectType());
    }

    /**
     * Classes.
     */

    #[Test]
    public function itGetsClassData()
    {
        $classes = $this->session->GetClassesMetadata('Property');
        $this->assertIsArray($classes);
        $this->assertSame(7, count($classes));
        $this->assertSame('A', reset($classes)->getClassName());
    }

    #[Test]
    public function itGetsRelatedTableData()
    {
        $classes = $this->session->GetClassesMetadata('Property');
        $this->assertIsArray($classes);
        $firstClass = Arr::first($classes);

        $this->assertSame('LIST_0', Arr::first($firstClass->getTable())->getSystemName());
    }

    #[Test]
    public function itGetsKeyedClassMetadata()
    {
        $classes = $this->session->GetClassesMetadata('Property');
        $this->assertInstanceOf(\PHRETS\Models\Metadata\ResourceClass::class, $classes['A']);
    }

    /**
     * Table.
     */

     #[Test]
    public function itGetsTableData()
    {
        $fields = $this->session->GetTableMetadata('Property', 'A');
        $this->assertTrue(count($fields) > 100, 'Verify that a lot of fields came back');
        $this->assertSame('LIST_0', Arr::first($fields)?->getSystemName());
    }

    #[Test]
    public function itSeesTableAttributes()
    {
        $fields = $this->session->GetTableMetadata('Property', 'A');
        $this->assertSame('Property', Arr::first($fields)?->getResource());
        $this->assertSame('A', Arr::last($fields)?->getClass());
    }

    #[Test]
    public function itSeesFieldsByKey()
    {
        $fields = $this->session->GetTableMetadata('Property', 'A');
        $this->assertSame('Listing ID', $fields['LIST_105']->getLongName());
    }

    #[Test]
    public function itSeesFieldsByStandardKey()
    {
        $fields = $this->session->GetTableMetadata('Property', 'A', 'StandardName');
        $this->assertSame('Listing ID', $fields['ListingID']->getLongName());
    }

    #[Test]
    public function itGetsObjectMetadata()
    {
        $object_types = $this->session->GetObjectMetadata('Property');
        $this->assertTrue(count($object_types) > 4, 'Verify that a few came back');
        $this->assertSame('Photo', Arr::first($object_types)?->getObjectType());
        $this->assertSame('LIST_133', Arr::first($object_types)->getObjectCount());
    }

    #[Test]
    public function itGetsKeyedObjectMetadata()
    {
        $object_types = $this->session->GetObjectMetadata('Property');
        $this->assertInstanceOf('\PHRETS\Models\Metadata\BaseObject', $object_types['Photo']);
    }

    /**
     * Lookups.
     */

    #[Test]
    public function itGetsLookupValues()
    {
        $values = $this->session->GetLookupValues('Property', '20000426151013376279000000');
        $first = Arr::first($values);

        $this->assertSame('Lake/Other', $first->getLongValue());
        $this->assertSame('5PSUX49PM1Q', $first->getValue());
    }

    #[Test]
    public function itGetsRelatedLookupValues()
    {
        $fields = $this->session->GetTableMetadata('Property', 'A');

        $quick_way = $fields['LIST_9']->getLookupValues();
        $manual_way = $this->session->GetLookupValues('Property', '20000426151013376279000000');

        $this->assertEquals(Arr::first($quick_way), Arr::first($manual_way));
    }

    #[Test]
    public function itRecoversFromBadLookuptypeTag()
    {
        $config = new \PHRETS\Configuration();
        $config->setLoginUrl('http://retsgw.flexmls.com/lookup/rets2_1/Login')
                ->setUsername(getenv('PHRETS_TESTING_USERNAME'))
                ->setPassword(getenv('PHRETS_TESTING_PASSWORD'))
                ->setRetsVersion('1.5');

        $session = $this->createSession($config);
        $session->Login();

        $values = $session->GetLookupValues('Property', '20000426151013376279000000');
        $this->assertCount(6, $values);
    }

    #[Test]
    public function itHandlesIncompleteObjectMetadataCorrectly()
    {
        $config = new \PHRETS\Configuration();
        $config->setLoginUrl('http://retsgw.flexmls.com/rets2_1/Login')
            ->setUsername(getenv('PHRETS_TESTING_USERNAME'))
            ->setPassword(getenv('PHRETS_TESTING_PASSWORD'))
            ->setRetsVersion('1.5');

        $session = $this->createSession($config);
        $session->Login();

        $values = $session->GetObjectMetadata('PropertyPowerProduction');
        $this->assertCount(0, $values);
    }
}
