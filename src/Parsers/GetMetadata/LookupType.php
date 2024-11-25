<?php

namespace PHRETS\Parsers\GetMetadata;

use PHRETS\Http\Response;
use PHRETS\Parsers\ParserType;
use PHRETS\Session;

class LookupType extends Base
{
    /**
     * @return list<\PHRETS\Models\Metadata\LookupType>
     */
    public function parse(Session $rets, Response $response): array
    {
        /** @var \PHRETS\Parsers\XML $parser */
        $parser = $rets->getConfiguration()->getStrategy()->provide(ParserType::XML);
        $xml = $parser->parse($response);

        $collection = [];

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
                $collection[] = $metadata;
            }
        }

        return $collection;
    }
}
