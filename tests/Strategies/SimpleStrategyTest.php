<?php

use PHPUnit\Framework\TestCase;
use PHRETS\Configuration;
use PHRETS\Strategies\SimpleStrategy;
use PHRETS\Strategies\StandardStrategy;

class SimpleStrategyTest extends TestCase
{
    /** @test **/
    public function itProvidesDefaults()
    {
        $config = new Configuration();
        $strategy = new SimpleStrategy();
        $strategy->initialize($config);

        $this->assertInstanceOf('\PHRETS\Parsers\Login\OneFive', $strategy->provide('parser.login'));
    }

    /** @test **/
    public function itProvidesA18LoginParser()
    {
        $config = new Configuration();
        $config->setRetsVersion('1.8');
        $strategy = new SimpleStrategy();
        $strategy->initialize($config);

        $this->assertInstanceOf('\PHRETS\Parsers\Login\OneEight', $strategy->provide('parser.login'));
    }

    /** @test **/
    public function itProvidesSingletons()
    {
        $config = new Configuration();
        $strategy = new SimpleStrategy();
        $strategy->initialize($config);

        $parser = $strategy->provide('parser.login');
        $another_parser = $strategy->provide('parser.login');

        $this->assertSame($parser, $another_parser);
    }
}
