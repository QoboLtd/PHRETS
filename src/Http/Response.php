<?php namespace PHRETS\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Class Response
 * @package PHRETS\Http
 *
 * @method ResponseInterface|StreamInterface getBody
 * @method array getHeaders
 */
class Response
{
	public function __construct(protected ResponseInterface $response)
 {
 }

	public function xml()
	{
		$body = (string) $this->response->getBody();

		// Remove any carriage return / newline in XML response.
		$body = trim($body);

		return new \SimpleXMLElement($body);
	}

	public function __call($method, $args = [])
	{
		return call_user_func_array([$this->response, $method], $args);
	}

	public function getHeader($name)
	{
		$headers = $this->response->getHeader($name);

		if ($headers) {
			return implode('; ', $headers);
		} else {
			return null;
		}
	}
}
