<?php
namespace PHRETS\Test\Integration\Parsers;

use PHRETS\Http\Response;
use PHRETS\Parsers\XML;
use SimpleXMLElement;

class CustomXMLParser extends XML
{
    public function parse(Response $response): SimpleXMLElement
    {
        $string = str_replace('LIST_1', 'LIST_10000', (string)$response->getBody());

        return new SimpleXMLElement($string);
    }
}
