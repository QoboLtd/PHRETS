<?php

namespace PHRETS\Parsers;

use PHRETS\Http\Response;

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
