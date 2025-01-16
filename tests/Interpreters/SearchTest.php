<?php
namespace PHRETS\Test\Interpreters;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PHRETS\Interpreters\Search;

class SearchTest extends TestCase
{
    #[Test]
    public function itDoesntTouchProperlyFormattedDmql(): void
    {
        $this->assertSame('(FIELD=VALUE)', Search::dmql('(FIELD=VALUE)'));
    }

    #[Test]
    public function itWrapsSimplifiedDmqlInParens(): void
    {
        $this->assertSame('(FIELD=VALUE)', Search::dmql('FIELD=VALUE'));
    }

    #[Test]
    public function itDoesntModifyWhenSpecialCharactersAreUsed(): void
    {
        $this->assertSame('*', Search::dmql('*'));
        $this->assertSame('', Search::dmql(''));
    }
}
