<?php

namespace PHRETS\Parsers\GetObject;

use GuzzleHttp\Psr7\Response;
use PHRETS\Http\Response as PHRETSResponse;
use ZBateson\MailMimeParser\Message;
use ZBateson\MailMimeParser\Message\IMimePart;

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

        // the RETS multipart body is a MIME entity missing its own header block,
        // so give it the Content-Type it was actually served with and let a real
        // MIME parser handle boundary detection, preamble/epilogue and encoding
        $contentType = (string) $response->getHeader('Content-Type');
        $message = Message::from("Content-Type: {$contentType}\r\n\r\n" . $body, false);

        $parser = new Single();

        $gatheredParts = [];
        foreach ($message->getAllAttachmentParts() as $part) {
            if (!$part instanceof IMimePart) {
                continue;
            }

            $headers = [];
            foreach ($part->getAllHeaders() as $header) {
                $headers[$header->getName()][] = $header->getRawValue();
            }

            $single = new PHRETSResponse(new Response(200, $headers, (string) $part->getContent()));
            $gatheredParts[] = $parser->parse($single);
        }

        return $gatheredParts;
    }
}
