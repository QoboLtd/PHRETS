<?php

namespace PHRETS\Parsers\GetMetadata;

use Illuminate\Support\Collection;
use PHRETS\Http\Response;
use PHRETS\Parsers\XML;
use PHRETS\Session;

class LookupType extends Base
{
    public function parse(Session $rets, Response $response): Collection
    {
        /** @var XML $parser */
        $parser = $rets->getConfiguration()->getStrategy()->provide(\PHRETS\Strategies\Strategy::PARSER_XML);
        $xml = $parser->parse($response);

        $collection = new Collection();

        if ($xml->METADATA) {
            // some servers don't name this correctly for the version of RETS used, so play nice with either way
            if (!empty($xml->METADATA->{'METADATA-LOOKUP_TYPE'}->LookupType)) {
                $base = $xml->METADATA->{'METADATA-LOOKUP_TYPE'}->LookupType;
            } else {
                $base = $xml->METADATA->{'METADATA-LOOKUP_TYPE'}->Lookup;
            }

            foreach ($base as $key => $value) {
                $metadata = new \PHRETS\Models\Metadata\LookupType();
                $metadata->setSession($rets);
                $this->loadFromXml($metadata, $value, $xml->METADATA->{'METADATA-LOOKUP_TYPE'});
                $collection->push($metadata);
            }
        }

        return $collection;
    }
}
