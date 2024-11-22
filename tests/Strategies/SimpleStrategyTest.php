<?php
namespace PHRETS\Test\Strategies;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PHRETS\Configuration;
use PHRETS\Parsers\ParserType;
use PHRETS\Strategies\SimpleStrategy;

class SimpleStrategyTest extends TestCase
{
    #[Test]
    public function itProvidesDefaults()
    {
        $config = new Configuration();
        $strategy = new SimpleStrategy();
        $strategy->initialize($config);

        $this->assertInstanceOf('\PHRETS\Parsers\Login\OneFive', $strategy->provide(ParserType::LOGIN));
    }

    #[Test]
    public function itProvidesA18LoginParser()
    {
        $config = new Configuration();
        $config->setRetsVersion('1.8');
        $strategy = new SimpleStrategy();
        $strategy->initialize($config);

        $this->assertInstanceOf('\PHRETS\Parsers\Login\OneEight', $strategy->provide(ParserType::LOGIN));
    }

    #[Test]
    public function itProvidesSingletons()
    {
        $config = new Configuration();
        $strategy = new SimpleStrategy();
        $strategy->initialize($config);

        $parser = $strategy->provide(ParserType::LOGIN);
        $another_parser = $strategy->provide(ParserType::LOGIN);

        $this->assertSame($parser, $another_parser);
    }
}
