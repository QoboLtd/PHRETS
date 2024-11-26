<?php namespace PHRETS\Parsers\PostObject;

use PHRETS\Http\Response;
use PHRETS\Session;

class OneEight
{
    /**
     * @return array<string,mixed>
     */
    public function parse(Session $rets, Response $response): array
    {
        $xml = $response->xml();

        $replyCode = (int) $xml['ReplyCode'];
        $replyText = (string) $xml['ReplyText'];
        $errors = [];

        if ($xml->ERRORBLOCK) {
            $errors = array_map(fn($line) => explode("\t", trim($line)), (array) $xml->ERRORBLOCK->ERRORDATA);
        }

        return [
            'response' => json_encode($xml),
            'code' => $replyCode,
            'message' => $replyText,
            'success' => $replyCode === 0,
            'errors' => $errors,
        ];
    }
}
