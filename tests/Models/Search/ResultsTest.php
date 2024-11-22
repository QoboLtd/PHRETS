<?php

use PHPUnit\Framework\TestCase;
use PHRETS\Models\Search\Record;
use PHRETS\Models\Search\Results;

class ResultsTest extends TestCase
{
    /** @var \PHRETS\Models\Search\Results */
    protected $rs;

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

    /** @test * */
    public function itHoldsRecords()
    {
        $this->assertCount(2, $this->rs);
    }

    /** @test * */
    public function itKeysRecords()
    {
        $this->rs->keyResultsBy('id');

        $this->assertSame('left', $this->rs->find(1)?->get('name'));
        $this->assertSame('right', $this->rs->find(2)?->get('name'));
        $this->assertNull($this->rs->find(3));
    }

    /** @test * */
    public function itKeysRecordsWithClosure()
    {
        $this->rs->keyResultsBy(
            function (Record $record) {
                return $record->get('id') . '_' . $record->get('name');
            }
        );

        $this->assertTrue(is_object($this->rs->find('1_left')));
        $this->assertSame('up', $this->rs->find('1_left')->get('value'));
    }

    /** @test **/
    public function itTraverses()
    {
        $found = false;
        foreach ($this->rs as $rs) {
            if ($rs->get('name') == 'right') {
                $found = true;
            }
        }
        $this->assertTrue($found);
    }

    /** @test **/
    public function itAssociatesMetadata()
    {
        $metadata = ['test', 'fields'];
        $rs = new Results();
        $rs->setMetadata($metadata);

        $this->assertSame($metadata, $rs->getMetadata());
    }

    /** @test **/
    public function itTracksHeaders()
    {
        $fields = ['A', 'B', 'C', 'D', 'E'];
        $rs = new Results();
        $rs->setHeaders($fields);

        $this->assertSame($fields, $rs->getHeaders());
    }

    /** @test **/
    public function itTracksCounts()
    {
        $rs = new Results();
        $rs->setTotalResultsCount(1000);
        $rs->setReturnedResultsCount(500);

        $this->assertSame(1000, $rs->getTotalResultsCount());
        $this->assertSame(500, $rs->getReturnedResultsCount());
    }

    /** @test **/
    public function itTracksResourcesAndClasses()
    {
        $rs = new Results();
        $rs->setResource('Property');
        $rs->setClass('A');

        $this->assertSame('Property', $rs->getResource());
        $this->assertSame('A', $rs->getClass());
    }

    /** @test **/
    public function itAllowsArrayAccessingKeyedResults()
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

    /** @test **/
    public function itHoldsErrors()
    {
        $rs = new Results();
        $rs->setError('test');
        $this->assertSame('test', $rs->getError());
    }

    /** @test **/
    public function itHoldsASession()
    {
        $rs = new Results();
        $config = new \PHRETS\Configuration();
        $config->setLoginUrl('https://www.test-rets.com/login');
        $session = new \PHRETS\Session($config);
        $rs->setSession($session);
        $this->assertSame($session, $rs->getSession());
    }

    /** @test **/
    public function itGivesAList()
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

    /** @test **/
    public function itGivesAListExcludingRestrictedValues()
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

    /** @test **/
    public function itConvertsObjectToJSON()
    {
        $expected = '[{"id":1,"name":"left","value":"up"},{"id":2,"name":"right","value":"down"}]';
        $this->assertSame($expected, json_encode($this->rs, JSON_THROW_ON_ERROR));
    }

    /** @test **/
    public function itConvertsObjectToArray()
    {
        $expected = [
            ['id' => 1, 'name' => 'left', 'value' => 'up'],
            ['id' => 2, 'name' => 'right', 'value' => 'down'],
        ];
        $this->assertSame($expected, $this->rs->toArray());
    }
}
