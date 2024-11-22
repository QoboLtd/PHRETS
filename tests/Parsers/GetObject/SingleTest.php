<?php
namespace PHRETS\Test\Parsers\GetObject;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PHRETS\Http\Response as PHRETSResponse;
use PHRETS\Parsers\GetObject\Single;

class SingleTest extends TestCase
{
    #[Test]
    public function itUnderstandsTheBasics()
    {
        $parser = new Single();
        $single = new PHRETSResponse(new Response(200, ['Content-Type' => 'text/plain'], 'Test'));
        $obj = $parser->parse($single);

        $this->assertSame('Test', $obj->getContent());
        $this->assertSame('text/plain', $obj->getContentType());
    }

    #[Test]
    public function itDetectsAndHandlesErrors()
    {
        $error = '<RETS ReplyCode="20203" ReplyText="RETS Server: Some error">
        Valid Classes are: A B C E F G H I
        </RETS>';
        $parser = new Single();
        $single = new PHRETSResponse(new Response(200, ['Content-Type' => 'text/xml'], $error));
        $obj = $parser->parse($single);

        $this->assertTrue($obj->isError());
        $this->assertSame('20203', $obj->getError()?->getCode());
        $this->assertSame('RETS Server: Some error', $obj->getError()->getMessage());
    }

    #[Test]
    public function itSeesTheNewRetsErrorHeader()
    {
        $error = '<RETS ReplyCode="20203" ReplyText="RETS Server: Some error">
        Valid Classes are: A B C E F G H I
        </RETS>';
        $parser = new Single();
        $single = new PHRETSResponse(new Response(200, ['Content-Type' => 'text/plain', 'RETS-Error' => '1'], $error));
        $obj = $parser->parse($single);

        $this->assertTrue($obj->isError());
    }
}
