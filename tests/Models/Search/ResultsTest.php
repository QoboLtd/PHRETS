<?php
namespace PHRETS\Test\Models\Search;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PHRETS\Models\Search\Record;
use PHRETS\Models\Search\Results;

class ResultsTest extends TestCase
{
    protected Results $rs;

    public function setUp(): void
    {
        $this->rs = new Results();

        $this->rs->setHeaders(['id', 'name', 'value']);

        $rc = new Record();
        $rc->set('id', 1);
        $rc->set('name', 'left');
        $rc->set('value', 'up');
        $this->rs->addRecord($rc);

        $rc = new Record();
        $rc->set('id', 2);
        $rc->set('name', 'right');
        $rc->set('value', 'down');
        $this->rs->addRecord($rc);
    }

    #[Test]
    public function itHoldsRecords(): void
    {
        $this->assertCount(2, $this->rs);
    }

    #[Test]
    public function itKeysRecords(): void
    {
        $this->rs->keyResultsBy('id');

        $this->assertSame('left', $this->rs->find(1)?->get('name'));
        $this->assertSame('right', $this->rs->find(2)?->get('name'));
        $this->assertNull($this->rs->find(3));
    }

    #[Test]
    public function itKeysRecordsWithClosure(): void
    {
        $this->rs->keyResultsBy(
            function (Record $record) {
                $id = $record->get('id');
                assert(is_string($id) || is_int($id));
                $name = $record->get('name');
                assert(is_string($name));
                return $id . '_' . $name;
            }
        );

        $this->assertTrue(is_object($this->rs->find('1_left')));
        $this->assertSame('up', $this->rs->find('1_left')->get('value'));
    }

    #[Test]
    public function itTraverses(): void
    {
        $found = false;
        foreach ($this->rs as $rs) {
            if ($rs->get('name') == 'right') {
                $found = true;
            }
        }
        $this->assertTrue($found);
    }

    #[Test]
    public function itAssociatesMetadata(): void
    {
        $metadata = ['test', 'fields'];
        $rs = new Results();
        $rs->setMetadata($metadata);

        $this->assertSame($metadata, $rs->getMetadata());
    }

    #[Test]
    public function itTracksHeaders(): void
    {
        $fields = ['A', 'B', 'C', 'D', 'E'];
        $rs = new Results();
        $rs->setHeaders($fields);

        $this->assertSame($fields, $rs->getHeaders());
    }

    #[Test]
    public function itTracksCounts(): void
    {
        $rs = new Results();
        $rs->setTotalResultsCount(1000);
        $rs->setReturnedResultsCount(500);

        $this->assertSame(1000, $rs->getTotalResultsCount());
        $this->assertSame(500, $rs->getReturnedResultsCount());
    }

    #[Test]
    public function itTracksResourcesAndClasses(): void
    {
        $rs = new Results();
        $rs->setResource('Property');
        $rs->setClass('A');

        $this->assertSame('Property', $rs->getResource());
        $this->assertSame('A', $rs->getClass());
    }

    #[Test]
    public function itAllowsArrayAccessingKeyedResults(): void
    {
        $r = new Record();
        $r->set('id', 'extra');
        $r->set('name', 'test');

        $this->rs['extra'] = $r;

        $r = new Record();
        $r->set('id', 'bonus');
        $r->set('name', 'test');
        $this->rs[] = $r;

        $this->rs->keyResultsBy('id');

        $this->assertSame('left', $this->rs['1']->get('name'));
        $this->assertFalse(isset($this->rs['bogus_record']));
        unset($this->rs['1']);
        $this->assertFalse(isset($this->rs['1']));
        $this->assertTrue(isset($this->rs['extra']));
        $this->assertTrue(isset($this->rs['bonus']));
    }

    #[Test]
    public function itHoldsErrors(): void
    {
        $rs = new Results();
        $rs->setError('test');
        $this->assertSame('test', $rs->getError());
    }

    #[Test]
    public function itHoldsASession(): void
    {
        $rs = new Results();
        $config = new \PHRETS\Configuration();
        $config->setLoginUrl('https://www.test-rets.com/login');
        $session = new \PHRETS\Session($config);
        $rs->setSession($session);
        $this->assertSame($session, $rs->getSession());
    }

    #[Test]
    public function itGivesAList(): void
    {
        $rs = new Results();

        $r = new Record();
        $r->set('id', 'extra');
        $r->set('name', 'test');
        $rs->addRecord($r);

        $r = new Record();
        $r->set('id', 'bonus');
        $r->set('name', 'test');
        $rs->addRecord($r);

        $r = new Record();
        $r->set('id', ''); // this is empty so it won't be included in the resulting list
        $r->set('name', 'another');
        $rs->addRecord($r);

        $this->assertSame(['extra', 'bonus'], $rs->lists('id'));
    }

    #[Test]
    public function itGivesAListExcludingRestrictedValues(): void
    {
        $rs = new Results();
        $rs->setRestrictedIndicator('****');

        $r = new Record();
        $r->set('id', 'extra');
        $r->set('name', 'test');
        $rs->addRecord($r);

        $r = new Record();
        $r->set('id', '****');
        $r->set('name', 'test');
        $rs->addRecord($r);

        $r = new Record();
        $r->set('id', 'bonus');
        $r->set('name', 'test');
        $rs->addRecord($r);

        $this->assertSame(['extra', 'bonus'], $rs->lists('id'));
    }

    #[Test]
    public function itConvertsObjectToJSON(): void
    {
        $expected = '[{"id":1,"name":"left","value":"up"},{"id":2,"name":"right","value":"down"}]';
        $this->assertSame($expected, json_encode($this->rs, JSON_THROW_ON_ERROR));
    }

    #[Test]
    public function itConvertsObjectToArray(): void
    {
        $expected = [
            ['id' => 1, 'name' => 'left', 'value' => 'up'],
            ['id' => 2, 'name' => 'right', 'value' => 'down'],
        ];
        $this->assertSame($expected, $this->rs->toArray());
    }
}
