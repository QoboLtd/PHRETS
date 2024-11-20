<?php

use PHRETS\Strategies\SimpleStrategy;
use PHRETS\Strategies\StandardStrategy;

class StrategyIntegrationTest extends BaseIntegration
{
    private function setParser(string $parser_name, $parser_object)
    {
        $strategy = $this->session->getConfiguration()->getStrategy();
        assert($strategy instanceof SimpleStrategy);

        $strategy->setInstance($parser_name, $parser_object);
    }

    /** @test */
    public function itSupportsCustomParsers()
    {
        $this->session->Login();

        /*
         * set a custom parser
         */
        $this->setParser(
            \PHRETS\Strategies\Strategy::PARSER_METADATA_SYSTEM,
            new CustomSystemParser()
        );

        $system = $this->session->GetSystemMetadata();

        $this->assertEquals('custom', $system->getSystemID());
    }

    /** @test */
    public function itSupportsCustomXmlParser()
    {
        $this->session->Login();

        /*
         * set a custom parser
         */
        $this->setParser(
            \PHRETS\Strategies\Strategy::PARSER_XML,
            new CustomXMLParser()
        );

        /** @var \PHRETS\Models\Search\Results $results */
        $results = $this->session->Search('Property', 'A', '*', ['Limit' => 3, 'Select' => ['LIST_1', 'LIST_105']]);
        $this->assertContains('LIST_10000', $results->getHeaders());
        $this->assertNotContains('LIST_1', $results->getHeaders());
    }
}
