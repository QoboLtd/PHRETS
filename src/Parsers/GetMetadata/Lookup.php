<?php namespace PHRETS\Parsers\GetMetadata;

use Illuminate\Support\Collection;
use PHRETS\Http\Response;
use PHRETS\Session;

class Lookup extends Base
{
    public function parse(Session $rets, Response $response)
    {
        /** @var \PHRETS\Parsers\XML $parser */
        $parser = $rets->getConfiguration()->getStrategy()->provide(\PHRETS\Strategies\Strategy::PARSER_XML);
        $xml = $parser->parse($response);

        $collection = new Collection;

        if ($xml->METADATA) {
            foreach ($xml->METADATA->{'METADATA-LOOKUP'}->Lookup as $key => $value) {
                $metadata = new \PHRETS\Models\Metadata\Lookup;
                $metadata->setSession($rets);
                $this->loadFromXml($metadata, $value, $xml->METADATA->{'METADATA-LOOKUP'});
                $collection->put($metadata->getLookupName(), $metadata);
            }
        }

        return $collection;
    }
}
