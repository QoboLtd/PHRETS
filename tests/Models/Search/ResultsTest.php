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
        self::assertCount(2, $this->rs);
    }

    #[Test]
    public function itKeysRecords(): void
    {
        $this->rs->keyResultsBy('id');

        self::assertSame('left', $this->rs->find(1)?->get('name'));
        self::assertSame('right', $this->rs->find(2)?->get('name'));
        self::assertNull($this->rs->find(3));
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

        self::assertTrue(is_object($this->rs->find('1_left')));
        self::assertSame('up', $this->rs->find('1_left')->get('value'));
    }

    #[Test]
    public function itTraverses(): void
    {
        $found = false;
        foreach ($this->rs as $rs) {
            if ($rs->get('name') === 'right') {
                $found = true;
            }
        }
        self::assertTrue($found);
    }

    #[Test]
    public function itAssociatesMetadata(): void
    {
        $metadata = ['test', 'fields'];
        $rs = new Results();
        $rs->setMetadata($metadata);

        self::assertSame($metadata, $rs->getMetadata());
    }

    #[Test]
    public function itTracksHeaders(): void
    {
        $fields = ['A', 'B', 'C', 'D', 'E'];
        $rs = new Results();
        $rs->setHeaders($fields);

        self::assertSame($fields, $rs->getHeaders());
    }

    #[Test]
    public function itTracksCounts(): void
    {
        $rs = new Results();
        $rs->setTotalResultsCount(1000);
        $rs->setReturnedResultsCount(500);

        self::assertSame(1000, $rs->getTotalResultsCount());
        self::assertSame(500, $rs->getReturnedResultsCount());
    }

    #[Test]
    public function itTracksResourcesAndClasses(): void
    {
        $rs = new Results();
        $rs->setResource('Property');
        $rs->setClass('A');

        self::assertSame('Property', $rs->getResource());
        self::assertSame('A', $rs->getClass());
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

        self::assertSame('left', $this->rs['1']->get('name'));
        self::assertFalse(isset($this->rs['bogus_record']));
        unset($this->rs['1']);
        self::assertFalse(isset($this->rs['1']));
        self::assertTrue(isset($this->rs['extra']));
        self::assertTrue(isset($this->rs['bonus']));
    }

    #[Test]
    public function itHoldsErrors(): void
    {
        $rs = new Results();
        $rs->setError('test');
        self::assertSame('test', $rs->getError());
    }

    #[Test]
    public function itHoldsASession(): void
    {
        $rs = new Results();
        $config = new \PHRETS\Configuration();
        $config->setLoginUrl('https://www.test-rets.com/login');
        $session = new \PHRETS\Session($config);
        $rs->setSession($session);
        self::assertSame($session, $rs->getSession());
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

        self::assertSame(['extra', 'bonus'], $rs->lists('id'));
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

        self::assertSame(['extra', 'bonus'], $rs->lists('id'));
    }

    #[Test]
    public function itConvertsObjectToJSON(): void
    {
        $expected = '[{"id":1,"name":"left","value":"up"},{"id":2,"name":"right","value":"down"}]';
        self::assertSame($expected, json_encode($this->rs, JSON_THROW_ON_ERROR));
    }

    #[Test]
    public function itConvertsObjectToArray(): void
    {
        $expected = [
            ['id' => 1, 'name' => 'left', 'value' => 'up'],
            ['id' => 2, 'name' => 'right', 'value' => 'down'],
        ];
        self::assertSame($expected, $this->rs->toArray());
    }
}
