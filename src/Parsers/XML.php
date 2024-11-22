<?php

namespace PHRETS\Parsers;

use PHRETS\Http\Response;
use Psr\Http\Message\ResponseInterface;

class XML
{
    /**
     * @throws \Exception
     */
    public function parse(Response $string): \SimpleXMLElement
    {
        return new \SimpleXMLElement((string)$string->getBody());
    }
}
