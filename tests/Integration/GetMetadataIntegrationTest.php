<?php
namespace PHRETS\Test\Integration;

use PHPUnit\Framework\Attributes\Test;
use PHRETS\Arr;
use PHRETS\Configuration;
use PHRETS\Enums\RETSVersion;

class GetMetadataIntegrationTest extends BaseIntegration
{
    /**
     * System.
     */

    #[Test]
    public function itGetsSystemData(): void
    {
        $system = $this->session->GetSystemMetadata();
        self::assertNotNull($system->getSystemID());
    }

    #[Test]
    public function itGetsSystemDataFor15(): void
    {
        $config = new Configuration(version: RETSVersion::VERSION_1_5);
        $config->setLoginUrl('http://retsgw.flexmls.com/rets2_1/Login')
                ->setUsername(getenv('PHRETS_TESTING_USERNAME'))
                ->setPassword(getenv('PHRETS_TESTING_PASSWORD'));

        $session = $this->createSession($config);
        $session->Login();

        $system = $session->GetSystemMetadata();
        self::assertSame('demomls', $system->getSystemID());
    }

    #[Test]
    public function itMakesAGoodUrl(): void
    {
        $this->session->GetSystemMetadata();
        self::assertSame(
            'http://retsgw.flexmls.com:80/rets2_1/GetMetadata?Type=METADATA-SYSTEM&ID=0&Format=STANDARD-XML',
            $this->session->getLastRequestURL()
        );
    }

    #[Test]
    public function itSeesSomeAttributes(): void
    {
        $system = $this->session->GetSystemMetadata();
        self::assertSame('demomls', $system->getSystemID());
        self::assertSame('-05:00', $system->getTimeZoneOffset());
    }

    #[Test]
    public function itGetsRelatedResources(): void
    {
        $system = $this->session->GetSystemMetadata()->getResources();
        $resources = $this->session->GetResourcesMetadata();
        self::assertEquals($system, $resources);
    }

    /**
     * Resources.
     */

    #[Test]
    public function itGetsResourceData(): void
    {
        $resources = $this->session->GetResourcesMetadata();
        self::assertArrayHasKey('Property', $resources);
        $resource = $resources['Property'];

        self::assertTrue($resource instanceof \PHRETS\Models\Metadata\Resource);
        self::assertSame('Property', $resource->getStandardName());
        self::assertSame('7', $resource->getClassCount());
    }

    #[Test]
    public function itGetsAllResourceData(): void
    {
        $resources = $this->session->GetResourcesMetadata();
        self::assertCount(9, $resources);
        self::assertSame('ActiveAgent', Arr::first($resources)?->getResourceID());
        self::assertSame('VirtualTour', Arr::last($resources)?->getResourceID());
    }

    #[Test]
    public function itGetsKeyedResourceData(): void
    {
        $resources = $this->session->GetResourcesMetadata();
        self::assertArrayHasKey('Property', $resources);
        self::assertInstanceOf(\PHRETS\Models\Metadata\Resource::class, $resources['Property']);
    }

    #[Test]
    public function itErrorsWithBadResourceName(): void
    {
        $resources = $this->session->GetResourcesMetadata();
        self::assertArrayNotHasKey('Bogus', $resources);
    }

    #[Test]
    public function itGetsRelatedClasses(): void
    {
        $resources = $this->session->GetResourcesMetadata();
        self::assertArrayHasKey('Property', $resources);

        $resource_classes = $resources['Property']->getClasses();
        $classes = $this->session->GetClassesMetadata('Property');
        self::assertEquals($resource_classes, $classes);
    }

    #[Test]
    public function itGetsRelatedObjectMetadata(): void
    {
        $resources = $this->session->GetResourcesMetadata();
        self::assertArrayHasKey('Property', $resources);

        $object_types = $resources['Property']->getObject();
        self::assertSame('Photo', Arr::first($object_types)?->getObjectType());
    }

    /**
     * Classes.
     */

    #[Test]
    public function itGetsClassData(): void
    {
        $classes = $this->session->GetClassesMetadata('Property');
        self::assertIsArray($classes);
        self::assertSame(7, count($classes));
        self::assertSame('A', reset($classes)->getClassName());
    }

    #[Test]
    public function itGetsRelatedTableData(): void
    {
        $classes = $this->session->GetClassesMetadata('Property');
        self::assertIsArray($classes);
        $firstClass = Arr::first($classes);

        self::assertSame('LIST_0', Arr::first($firstClass->getTable())->getSystemName());
    }

    #[Test]
    public function itGetsKeyedClassMetadata(): void
    {
        $classes = $this->session->GetClassesMetadata('Property');
        self::assertInstanceOf(\PHRETS\Models\Metadata\ResourceClass::class, $classes['A']);
    }

    /**
     * Table.
     */

     #[Test]
    public function itGetsTableData(): void
    {
        $fields = $this->session->GetTableMetadata('Property', 'A');
        self::assertTrue(count($fields) > 100, 'Verify that a lot of fields came back');
        self::assertSame('LIST_0', Arr::first($fields)?->getSystemName());
    }

    #[Test]
    public function itSeesTableAttributes(): void
    {
        $fields = $this->session->GetTableMetadata('Property', 'A');
        self::assertSame('Property', Arr::first($fields)?->getResource());
        self::assertSame('A', Arr::last($fields)?->getClass());
    }

    #[Test]
    public function itSeesFieldsByKey(): void
    {
        $fields = $this->session->GetTableMetadata('Property', 'A');
        self::assertSame('Listing ID', $fields['LIST_105']->getLongName());
    }

    #[Test]
    public function itSeesFieldsByStandardKey(): void
    {
        $fields = $this->session->GetTableMetadata('Property', 'A', 'StandardName');
        self::assertSame('Listing ID', $fields['ListingID']->getLongName());
    }

    #[Test]
    public function itGetsObjectMetadata(): void
    {
        $object_types = $this->session->GetObjectMetadata('Property');
        self::assertTrue(count($object_types) > 4, 'Verify that a few came back');
        self::assertSame('Photo', Arr::first($object_types)?->getObjectType());
        self::assertSame('LIST_133', Arr::first($object_types)->getObjectCount());
    }

    #[Test]
    public function itGetsKeyedObjectMetadata(): void
    {
        $object_types = $this->session->GetObjectMetadata('Property');
        self::assertInstanceOf('\PHRETS\Models\Metadata\BaseObject', $object_types['Photo']);
    }

    /**
     * Lookups.
     */

    #[Test]
    public function itGetsLookupValues(): void
    {
        $values = $this->session->GetLookupValues('Property', '20000426151013376279000000');
        $first = Arr::first($values);

        self::assertSame('Lake/Other', $first->getLongValue());
        self::assertSame('5PSUX49PM1Q', $first->getValue());
    }

    #[Test]
    public function itGetsRelatedLookupValues(): void
    {
        $fields = $this->session->GetTableMetadata('Property', 'A');

        $quick_way = $fields['LIST_9']->getLookupValues();
        $manual_way = $this->session->GetLookupValues('Property', '20000426151013376279000000');

        self::assertEquals(Arr::first($quick_way), Arr::first($manual_way));
    }

    #[Test]
    public function itRecoversFromBadLookuptypeTag(): void
    {
        $config = new Configuration(version: RETSVersion::VERSION_1_5);
        $config->setLoginUrl('http://retsgw.flexmls.com/lookup/rets2_1/Login')
                ->setUsername(getenv('PHRETS_TESTING_USERNAME'))
                ->setPassword(getenv('PHRETS_TESTING_PASSWORD'));

        $session = $this->createSession($config);
        $session->Login();

        $values = $session->GetLookupValues('Property', '20000426151013376279000000');
        self::assertCount(6, $values);
    }

    #[Test]
    public function itHandlesIncompleteObjectMetadataCorrectly(): void
    {
        $config = new \PHRETS\Configuration(version: RETSVersion::VERSION_1_5);
        $config->setLoginUrl('http://retsgw.flexmls.com/rets2_1/Login')
            ->setUsername(getenv('PHRETS_TESTING_USERNAME'))
            ->setPassword(getenv('PHRETS_TESTING_PASSWORD'));

        $session = $this->createSession($config);
        $session->Login();

        $values = $session->GetObjectMetadata('PropertyPowerProduction');
        self::assertCount(0, $values);
    }
}
