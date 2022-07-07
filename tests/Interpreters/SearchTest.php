<?php

use PHPUnit\Framework\TestCase;
use PHRETS\Interpreters\Search;

class SearchTest extends TestCase
{
    /** @test **/
    public function itDoesntTouchProperlyFormattedDmql()
    {
        $this->assertSame('(FIELD=VALUE)', Search::dmql('(FIELD=VALUE)'));
    }

    /** @test **/
    public function itWrapsSimplifiedDmqlInParens()
    {
        $this->assertSame('(FIELD=VALUE)', Search::dmql('FIELD=VALUE'));
    }

    /** @test **/
    public function itDoesntModifyWhenSpecialCharactersAreUsed()
    {
        $this->assertSame('*', Search::dmql('*'));
        $this->assertSame('', Search::dmql(''));
    }
}
