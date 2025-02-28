<?php
namespace PHRETS\Test\Parsers\Login;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PHRETS\Parsers\Login\OneEight;

class OneEightTest extends TestCase
{
    /** @var \PHRETS\Parsers\Login\OneEight */
    protected $parser;

    public function setUp(): void
    {
        $this->parser = new OneEight();
        $this->parser->parse('
Info=MEMBERNAME;Character;
Info=USERID;Character;1234567890
Info=USERLEVEL;Int;25
Info=USERCLASS;Character;RT
Info=AGENTCODE;Character;RESOWG
Info=BROKERCODE;Character;Test
Info=BROKERBRANCH;Character;Test01
Info=METADATAID;Character;12_34_56_78_ABCD_EFG
Info=METADATAVERSION;Character;37.86.72100
Info=METADATATIMESTAMP;DateTime;2014-06-30T18:41:40Z
Info=MINMETADATATIMESTAMP;DateTime;2014-06-30T18:41:40Z
Info=BOARD;Character;
Info=BROKERRECIPFLAG;Boolean;0
Info=MAINOFF;Character;test test
Info=OFFICE;Character;Test00
Info=SUL;Int;11
Info=UC;Character;RT
Info=USER;Character;RESOWG
ChangePassword=/ChangePassword.asmx/ChangePassword
GetObject=/GetObject.asmx/GetObject
Login=/Login.asmx/Login
Logout=/Logout.asmx/Logout
Search=/Search.asmx/Search
GetMetadata=/GetMetadata.asmx/GetMetadata
GetPayloadList=/GetPayloadList.asmx/GetPayloadList
        ');
    }

    #[Test]
    public function itSeesAllTransactions(): void
    {
        $this->assertSame(7, count($this->parser->getCapabilities()));
    }

    #[Test]
    public function itSeesCoreTransactions(): void
    {
        $this->assertSame('/Search.asmx/Search', $this->parser->getCapabilities()['Search']);
        $this->assertSame('/Logout.asmx/Logout', $this->parser->getCapabilities()['Logout']);
    }

    #[Test]
    public function itSeesAllDetails(): void
    {
        $this->assertSame(18, count($this->parser->getDetails()));
    }

    #[Test]
    public function itSeesUserDetails(): void
    {
        $this->assertSame('RESOWG', $this->parser->getDetails()['USER']);
    }

    #[Test]
    public function itCastsDetails(): void
    {
        $this->assertIsBool($this->parser->getDetails()['BROKERRECIPFLAG']);
        $this->assertIsInt($this->parser->getDetails()['SUL']);
    }
}
