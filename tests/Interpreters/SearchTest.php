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
        self::assertSame('(FIELD=VALUE)', Search::dmql('(FIELD=VALUE)'));
    }

    #[Test]
    public function itWrapsSimplifiedDmqlInParens(): void
    {
        self::assertSame('(FIELD=VALUE)', Search::dmql('FIELD=VALUE'));
    }

    #[Test]
    public function itDoesntModifyWhenSpecialCharactersAreUsed(): void
    {
        self::assertSame('*', Search::dmql('*'));
        self::assertSame('', Search::dmql(''));
    }
}
