<?php

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PHRETS\Models\Search\Record;
use PHRETS\Models\Search\Results;

class RecordTest extends TestCase
{
    #[Test]
    public function itHoldsValues()
    {
        $r = new Record();
        $r->set('name', 'value');

        $this->assertSame('value', $r->get('name'));
    }

    #[Test]
    public function itHoldsMultipleValues()
    {
        $r = new Record();
        $r->set('one', '1');
        $r->set(2, 'two');
        $r->set(3, 'three');

        $this->assertSame('1', $r->get('one'));
        $this->assertSame('two', $r->get(2));
        $this->assertSame('three', $r->get('3'));
    }

    #[Test]
    public function itDetectsRestrictedValues()
    {
        $rs = new Results();
        $rs->setRestrictedIndicator('RESTRICTED');

        $r = new Record();
        $r->set('name', 'value');
        $r->set('another', $rs->getRestrictedIndicator());
        $rs->addRecord($r);

        $this->assertFalse($r->isRestricted('name'));
        $this->assertTrue($r->isRestricted('another'));
    }

    #[Test]
    public function itChangesToArray()
    {
        $r = new Record();
        $r->set('ListingID', '123456789');
        $r->set('MLS', 'demo');

        $this->assertSame(['ListingID' => '123456789', 'MLS' => 'demo'], $r->toArray());
    }

    #[Test]
    public function itChangesToJson()
    {
        $r = new Record();
        $r->set('ListingID', '123456789');
        $r->set('MLS', 'demo');

        $this->assertSame('{"ListingID":"123456789","MLS":"demo"}', json_encode($r, JSON_THROW_ON_ERROR));
        $this->assertSame('{"ListingID":"123456789","MLS":"demo"}', (string) $r);
    }

    #[Test]
    public function itAccessesParentGivenAttributes()
    {
        $rs = new Results();
        $rs->setResource('Property');
        $rs->setClass('A');
        $rs->setHeaders(['LIST_1', 'LIST_2', 'LIST_3']);

        $r = new Record();
        $rs->addRecord($r);

        foreach ($rs as $r) {
            $this->assertSame('Property', $r->getResource());
            $this->assertSame('A', $r->getClass());
            $this->assertSame(['LIST_1', 'LIST_2', 'LIST_3'], $r->getFields());
        }
    }

    #[Test]
    public function itAllowsArrayAccess()
    {
        $r = new Record();
        $r->set('one', '1');
        $r->set(2, 'two');
        $r->set(3, 'three');
        $r['something'] = 'else';
        $r['to'] = 'remove';
        unset($r['to']);

        $this->assertSame('1', $r['one']);
        $this->assertFalse(isset($r['bogus']));
        $this->assertNull($r['bogus']);
        $this->assertSame('else', $r['something']);
        $this->assertNull($r['to']);
    }
}
