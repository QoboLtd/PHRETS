<?php
namespace PHRETS\Test\Parsers\GetObject;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PHRETS\Http\Response as PHRETSResponse;
use PHRETS\Parsers\GetObject\Multiple;

class MultipleTest extends TestCase
{
    #[Test]
    public function itBreaksThingsApart(): void
    {
        $headers = [
            'Server' => [
                0 => 'Apache-Coyote/1.1',
            ],
            'Cache-Control' => [
                0 => 'private',
            ],
            'RETS-Version' => [
                0 => 'RETS/1.5',
            ],
            'MIME-Version' => [
                0 => '1.0',
            ],
            'Content-Type' => [
                0 => 'multipart/parallel; boundary="FLEXeTNY6TFTGAwV1agjJsFyFogbnfoS1dm6y489g08F2TjwZWzQEW"',
            ],
            'Content-Length' => [
                0 => '1249',
            ],
            'Date' => [
                0 => 'Mon, 09 Jun 2014 00:10:51 GMT',
            ],
        ];

        $contents = file_get_contents('tests/Fixtures/GetObject/Multiple1.txt');
        $this->assertNotFalse($contents);
        $body = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);

        $parser = new Multiple();
        $collection = $parser->parse(new PHRETSResponse(new Response(200, $headers, $body)));

        $this->assertSame(5, count($collection));

        $obj = $collection[0];
        $this->assertSame('Exterior Main View', $obj->getContentDescription());
        $this->assertSame('http://url1.jpg', $obj->getLocation());
        $this->assertSame('http://url1.jpg', $obj->getHeader('Location'));
    }

    #[Test]
    public function itHandlesEmptyBodies(): void
    {
        $parser = new Multiple();
        $collection = $parser->parse(new PHRETSResponse(new Response(200, [], null)));
        $this->assertCount(0, $collection);
    }

    #[Test]
    public function itHandlesUnquotedBoundaries(): void
    {
        $headers = [
            'Server' => [
                0 => 'Apache-Coyote/1.1',
            ],
            'Cache-Control' => [
                0 => 'private',
            ],
            'RETS-Version' => [
                0 => 'RETS/1.5',
            ],
            'MIME-Version' => [
                0 => '1.0',
            ],
            'Content-Type' => [
                0 => 'multipart/parallel; boundary=FLEXeTNY6TFTGAwV1agjJsFyFogbnfoS1dm6y489g08F2TjwZWzQEW',
            ],
            'Content-Length' => [
                0 => '1249',
            ],
            'Date' => [
                0 => 'Mon, 09 Jun 2014 00:10:51 GMT',
            ],
        ];
        $body = json_decode(file_get_contents('tests/Fixtures/GetObject/Multiple1.txt', true));

        $parser = new Multiple();
        $collection = $parser->parse(new PHRETSResponse(new Response(200, $headers, $body)));

        $this->assertSame(5, count($collection));

        $obj = $collection[0];
        $this->assertSame('Exterior Main View', $obj->getContentDescription());
        $this->assertSame('http://url1.jpg', $obj->getLocation());
    }
}
