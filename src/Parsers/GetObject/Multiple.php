<?php

namespace PHRETS\Parsers\GetObject;

use GuzzleHttp\Psr7\Response;
use PHRETS\Http\Response as PHRETSResponse;

class Multiple
{
    /**
     * @return list<\PHRETS\Models\BaseObject>
     */
    public function parse(PHRETSResponse $response): array
    {
        $body = (string)$response->getBody();
        if ($body === '') {
            return [];
        }

        // help bad responses be more multipart compliant
        $body = "\r\n" . $body . "\r\n";

        // multipart
        preg_match('/boundary\=\"(.*?)\"/', (string) $response->getHeader('Content-Type'), $matches);
        if (isset($matches[1])) {
            $boundary = $matches[1];
        } else {
            preg_match('/boundary\=(.*?)(\s|$|\;)/', (string) $response->getHeader('Content-Type'), $matches);
            $boundary = $matches[1] ?? null;
        }
        // strip quotes off of the boundary
        $boundary = preg_replace('/^\"(.*?)\"$/', '\1', (string) $boundary);

        // clean up the body to remove a reamble and epilogue
        $body = preg_replace('/^(.*?)\r\n--' . $boundary . '\r\n/', "\r\n--{$boundary}\r\n", $body);
        assert($body !== null);
        // make the last one look like the rest for easier parsing
        $body = preg_replace('/\r\n--' . $boundary . '--/', "\r\n--{$boundary}\r\n", $body);
        assert($body !== null);

        // cut up the message
        $multi_parts = explode("\r\n--{$boundary}\r\n", $body);
        // take off anything that happens before the first boundary (the preamble)
        array_shift($multi_parts);
        // take off anything after the last boundary (the epilogue)
        array_pop($multi_parts);

        $parser = new Single();

        $gatheredParts = [];
        // go through each part of the multipart message
        foreach ($multi_parts as $part) {
            // get Guzzle to parse this multipart section as if it's a whole HTTP message
            $parts = \GuzzleHttp\Psr7\Message::parseResponse("HTTP/1.1 200 OK\r\n" . $part . "\r\n");

            // now throw this single faked message through the Single GetObject response parser
            $single = new PHRETSResponse(
                new Response($parts->getStatusCode(), $parts->getHeaders(), (string) $parts->getBody())
            );
            $obj = $parser->parse($single);

            // add information about this multipart to the returned collection
            $gatheredParts[] = $obj;
        }

        return $gatheredParts;
    }
}
