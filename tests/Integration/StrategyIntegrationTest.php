<?php
namespace PHRETS\Test\Integration;

use PHPUnit\Framework\Attributes\Test;
use PHRETS\Parsers\ParserType;
use PHRETS\Strategies\SimpleStrategy;
use PHRETS\Test\Integration\Parsers\CustomSystemParser;
use PHRETS\Test\Integration\Parsers\CustomXMLParser;

class StrategyIntegrationTest extends BaseIntegration
{
    private function setParser(ParserType $parser, mixed $parser_object): void
    {
        assert($this->session !== null);
        $strategy = $this->session->getConfiguration()->getStrategy();
        assert($strategy instanceof SimpleStrategy);

        $strategy->setInstance($parser, $parser_object);
    }

    #[Test]
    public function itSupportsCustomParsers()
    {
        assert($this->session !== null);
        $this->session->Login();

        /*
         * set a custom parser
         */
        $this->setParser(
            ParserType::METADATA_SYSTEM,
            new CustomSystemParser()
        );

        $system = $this->session->GetSystemMetadata();

        $this->assertEquals('custom', $system->getSystemID());
    }

    #[Test]
    public function itSupportsCustomXmlParser()
    {
        assert($this->session !== null);
        $this->session->Login();

        /*
         * set a custom parser
         */
        $this->setParser(
            ParserType::XML,
            new CustomXMLParser()
        );

        $results = $this->session->Search('Property', 'A', '*', ['Limit' => 3, 'Select' => ['LIST_1', 'LIST_105']]);
        $this->assertContains('LIST_10000', $results->getHeaders());
        $this->assertNotContains('LIST_1', $results->getHeaders());
    }
}
