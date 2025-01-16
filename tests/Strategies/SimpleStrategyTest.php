<?php
namespace PHRETS\Test\Strategies;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PHRETS\Configuration;
use PHRETS\Enums\RETSVersion;
use PHRETS\Parsers\ParserType;
use PHRETS\Strategies\SimpleStrategy;

class SimpleStrategyTest extends TestCase
{
    #[Test]
    public function itProvidesDefaults(): void
    {
        $config = new Configuration();
        $strategy = new SimpleStrategy();
        $strategy->initialize($config);

        $this->assertInstanceOf(\PHRETS\Parsers\Login\OneFive::class, $strategy->provide(ParserType::LOGIN));
    }

    #[Test]
    public function itProvidesA18LoginParser(): void
    {
        $config = new Configuration(version: RETSVersion::VERSION_1_8);
        $strategy = new SimpleStrategy();
        $strategy->initialize($config);

        $this->assertInstanceOf(\PHRETS\Parsers\Login\OneEight::class, $strategy->provide(ParserType::LOGIN));
    }

    #[Test]
    public function itProvidesSingletons(): void
    {
        $config = new Configuration();
        $strategy = new SimpleStrategy();
        $strategy->initialize($config);

        $parser = $strategy->provide(ParserType::LOGIN);
        $another_parser = $strategy->provide(ParserType::LOGIN);

        $this->assertSame($parser, $another_parser);
    }
}
