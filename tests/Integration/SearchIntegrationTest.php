<?php
namespace PHRETS\Test\Integration;

use PHPUnit\Framework\Attributes\Test;
use PHRETS\Arr;

class SearchIntegrationTest extends BaseIntegration
{
    #[Test]
    public function itMakesRequests()
    {
        $results = $this->session->Search('Property', 'A', '*', ['Select' => $this->search_select, 'Limit' => 3]);
        $this->assertCount(3, $results);
    }

    #[Test]
    public function itParsesRequests()
    {
        $results = $this->session->Search('Property', 'A', '*', ['Select' => $this->search_select, 'Limit' => 3]);

        $record = $results->first();

        $this->assertSame('20000426143505724628000000', $record->get('LIST_0'));

        $record = $results->last();

        $this->assertTrue(preg_match('/000000$/', $record->get('LIST_1')) === 1);
    }

    #[Test]
    public function itCountsRecords()
    {
        $results = $this->session->Search('Property', 'A', '*', ['Select' => $this->search_select, 'Limit' => 3]);

        $this->assertSame(3, $results->getReturnedResultsCount());
        $this->assertSame(9057, $results->getTotalResultsCount());
    }

    #[Test]
    public function itSeesMaxrowsReached()
    {
        $results = $this->session->Search('Property', 'A', '*', ['Select' => $this->search_select, 'Limit' => 3]);

        $this->assertTrue($results->isMaxRowsReached());
    }

    #[Test]
    public function itLimitsFields()
    {
        /** @var \PHRETS\Models\Search\Results $results */
        $results = $this->session->Search('Property', 'A', '*', ['Limit' => 3, 'Select' => 'LIST_1,LIST_105']);
        $this->assertContains('LIST_1', $results->getHeaders());
        $this->assertCount(2, $results->getHeaders());
        $this->assertNotContains('LIST_22', $results->getHeaders());
    }

    #[Test]
    public function itLimitsFieldsWithAnArray()
    {
        /** @var \PHRETS\Models\Search\Results $results */
        $results = $this->session->Search('Property', 'A', '*', ['Limit' => 3, 'Select' => ['LIST_1', 'LIST_105']]);
        $this->assertContains('LIST_1', $results->getHeaders());
        $this->assertCount(2, $results->getHeaders());
        $this->assertNotContains('LIST_22', $results->getHeaders());
    }

    #[Test]
    public function itProvidesAccessToAssociatedMetadata()
    {
        $results = $this->session->Search('Property', 'A', '*', ['Limit' => 3, 'Select' => ['LIST_1', 'LIST_105']]);
        $this->assertInstanceOf(\PHRETS\Models\Metadata\Table::class, Arr::first($results->getMetadata()));
    }

    #[Test]
    public function itRecursivelyRetrievesAllResults()
    {
        $this->session->Login();

        $results = $this->session->Search(
            'Property',
            'A',
            '(LIST_22=90000000+)',
            ['Limit' => '15', 'Select' => 'LIST_1'],
            true
        );

        $this->assertCount(40, $results);
    }

    #[Test]
    public function itRecoversFromMissingDelimiter()
    {
        $this->session->Login();

        // this is manually faked in the fixture
        $results = $this->session->Search(
            'Property',
            'BROKENDELIMITER',
            '(LIST_22=90000000+)',
            ['Limit' => '15', 'Select' => 'LIST_1']
        );

        $this->assertCount(1, $results->getHeaders());
    }

    #[Test]
    public function itDoesntDieWhenNoCountIsGiven()
    {
        $this->session->Login();

        // this is manually faked in the fixtures
        $results = $this->session->Search(
            'Property',
            'NOCOUNT',
            '(LIST_22=90000000+)',
            ['Limit' => '15', 'Select' => 'LIST_1'],
            true
        );

        $this->assertCount(40, $results);
    }

    #[Test]
    public function itDetectsBrokenPagination()
    {
        $this->expectException(\PHRETS\Exceptions\AutomaticPaginationError::class);
        $this->session->Login();

        // this is manually faked in the fixture
        $this->session->Search(
            'Property',
            'BROKENPAGINATION',
            '(LIST_22=90000000+)',
            ['Limit' => '15', 'Select' => 'LIST_1'],
            true
        );
    }
}
