<?php

namespace PHRETS\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use SimpleXMLElement;

/**
 * Class Response.
 *
 */
class Response
{
    public function __construct(protected ResponseInterface $response)
    {
    }

    /**
     * @throws \Exception
     */
    public function xml(): SimpleXMLElement
    {
        $body = (string) $this->response->getBody();

        // Remove any carriage return / newline in XML response.
        $body = trim($body);

        return new SimpleXMLElement($body);
    }

    public function getBody(): StreamInterface
    {
        return $this->response->getBody();
    }

    public function getStatusCode(): int
    {
        return $this->response->getStatusCode();
    }

    /**
     * @return array<string,array<int,string>>
     */
    public function getHeaders(): array
    {
        return $this->response->getHeaders();
    }

    public function getHeader(string $name): ?string
    {
        $headers = $this->response->getHeader($name);

        if ($headers !== []) {
            return implode('; ', $headers);
        } else {
            return null;
        }
    }
}
