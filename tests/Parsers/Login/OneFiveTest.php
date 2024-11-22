<?php
namespace PHRETS\Test\Parsers\Login;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PHRETS\Parsers\Login\OneFive;

class OneFiveTest extends TestCase
{
    /** @var \PHRETS\Parsers\Login\OneFive */
    protected $parser;

    public function setUp(): void
    {
        $this->parser = new OneFive();
        $this->parser->parse('
MemberName=UNKNOWN
User=unk,MASTER,4,1234567890
Broker=UNKNOWN
MetadataVersion=01.03.55606
MinMetadataVersion=01.03.55606
Login=/rets1_5/Login
Search=/rets1_5/Search
GetMetadata=/rets1_5/GetMetadata
X-SampleLinks=/rets1_5/Links
GetObject=/rets1_5/GetObject
Logout=/rets1_5/Logout
        ');
    }

    #[Test]
    public function itSeesAllTransactions()
    {
        $this->assertSame(6, count($this->parser->getCapabilities()));
    }

    #[Test]
    public function itSeesCoreTransactions()
    {
        $this->assertSame('/rets1_5/Search', $this->parser->getCapabilities()['Search']);
        $this->assertSame('/rets1_5/Logout', $this->parser->getCapabilities()['Logout']);
    }

    #[Test]
    public function itSeesCustomTransactions()
    {
        $this->assertSame('/rets1_5/Links', $this->parser->getCapabilities()['X-SampleLinks']);
    }

    #[Test]
    public function itSeesAllDetails()
    {
        $this->assertSame(5, count($this->parser->getDetails()));
    }

    #[Test]
    public function itSeesUserDetails()
    {
        $this->assertSame('unk,MASTER,4,1234567890', $this->parser->getDetails()['User']);
    }
}
