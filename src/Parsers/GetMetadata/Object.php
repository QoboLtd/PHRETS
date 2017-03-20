<?php namespace PHRETS\Parsers\GetMetadata;

use GuzzleHttp\Message\ResponseInterface;
use Illuminate\Support\Collection;
use PHRETS\Session;

class Object extends Base
{
    public function parse(Session $rets, ResponseInterface $response)
    {
        $xml = $response->xml();

        $collection = new Collection;

        if ($xml->METADATA) {
            if (isset($xml->METADATA->{'METADATA-OBJECT'}) and is_iterable($xml->METADATA->{'METADATA-OBJECT'}->Object)) {
                foreach ($xml->METADATA->{'METADATA-OBJECT'}->Object as $key => $value) {
                    $metadata = new \PHRETS\Models\Metadata\Object;
                    $metadata->setSession($rets);
                    $obj = $this->loadFromXml($metadata, $value, $xml->METADATA->{'METADATA-OBJECT'});
                    $collection->put($obj->getObjectType(), $obj);
                }
            }
        }

        return $collection;
    }
}
