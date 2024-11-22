<?php

use PHRETS\Http\Response;
use PHRETS\Parsers\XML;

class CustomXMLParser extends XML
{
    public function parse(Response $response): SimpleXMLElement
    {
        $string = str_replace('LIST_1', 'LIST_10000', (string)$response->getBody());

        return new \SimpleXMLElement((string) $string);
    }
}
