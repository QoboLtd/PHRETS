<?php

namespace PHRETS\Parsers;

use Exception;
use PHRETS\Http\Response;
use Psr\Http\Message\ResponseInterface;

class XML
{
    /**
     * @throws Exception
     */
    public function parse($string): \SimpleXMLElement
    {
        if ($string instanceof ResponseInterface || $string instanceof Response) {
            $string = $string->getBody()->__toString();
        }

        return new \SimpleXMLElement((string) $string);
    }
}
