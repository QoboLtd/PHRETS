<?php
namespace PHRETS\Test\Models\Search;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PHRETS\Models\Search\Record;
use PHRETS\Models\Search\Results;

class RecordTest extends TestCase
{
    #[Test]
    public function itHoldsValues(): void
    {
        $r = new Record();
        $r->set('name', 'value');

        self::assertSame('value', $r->get('name'));
    }

    #[Test]
    public function itHoldsMultipleValues(): void
    {
        $r = new Record();
        $r->set('one', '1');
        $r->set(2, 'two');
        $r->set(3, 'three');

        self::assertSame('1', $r->get('one'));
        self::assertSame('two', $r->get(2));
        self::assertSame('three', $r->get('3'));
    }

    #[Test]
    public function itDetectsRestrictedValues(): void
    {
        $rs = new Results();
        $rs->setRestrictedIndicator('RESTRICTED');

        $r = new Record();
        $r->set('name', 'value');
        $r->set('another', $rs->getRestrictedIndicator());
        $rs->addRecord($r);

        self::assertFalse($r->isRestricted('name'));
        self::assertTrue($r->isRestricted('another'));
    }

    #[Test]
    public function itChangesToArray(): void
    {
        $r = new Record();
        $r->set('ListingID', '123456789');
        $r->set('MLS', 'demo');

        self::assertSame(['ListingID' => '123456789', 'MLS' => 'demo'], $r->toArray());
    }

    #[Test]
    public function itChangesToJson(): void
    {
        $r = new Record();
        $r->set('ListingID', '123456789');
        $r->set('MLS', 'demo');

        self::assertSame('{"ListingID":"123456789","MLS":"demo"}', json_encode($r, JSON_THROW_ON_ERROR));
        self::assertSame('{"ListingID":"123456789","MLS":"demo"}', (string) $r);
    }

    #[Test]
    public function itAccessesParentGivenAttributes(): void
    {
        $rs = new Results();
        $rs->setResource('Property');
        $rs->setClass('A');
        $rs->setHeaders(['LIST_1', 'LIST_2', 'LIST_3']);

        $rs->addRecord(new Record());

        foreach ($rs as $r) {
            self::assertSame('Property', $r->getResource());
            self::assertSame('A', $r->getClass());
            self::assertSame(['LIST_1', 'LIST_2', 'LIST_3'], $r->getFields());
        }
    }

    #[Test]
    public function itAllowsArrayAccess(): void
    {
        $r = new Record();
        $r->set('one', '1');
        $r->set(2, 'two');
        $r->set(3, 'three');
        $r['something'] = 'else';
        $r['to'] = 'remove';
        unset($r['to']);

        self::assertSame('1', $r['one']);
        self::assertFalse(isset($r['bogus']));
        self::assertNull($r['bogus']);
        self::assertSame('else', $r['something']);
        self::assertNull($r['to']);
    }
}
