<?php

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PHRETS\Interpreters\Search;

class SearchTest extends TestCase
{
    #[Test]
    public function itDoesntTouchProperlyFormattedDmql()
    {
        $this->assertSame('(FIELD=VALUE)', Search::dmql('(FIELD=VALUE)'));
    }

    #[Test]
    public function itWrapsSimplifiedDmqlInParens()
    {
        $this->assertSame('(FIELD=VALUE)', Search::dmql('FIELD=VALUE'));
    }

    #[Test]
    public function itDoesntModifyWhenSpecialCharactersAreUsed()
    {
        $this->assertSame('*', Search::dmql('*'));
        $this->assertSame('', Search::dmql(''));
    }
}
