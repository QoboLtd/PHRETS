<?php
namespace PHRETS\Parsers\GetMetadata;

use PHRETS\Http\Response;
use PHRETS\Parsers\ParserType;
use PHRETS\Session;

class Lookup extends Base
{
    /**
     * @return array<string,\PHRETS\Models\Metadata\Lookup>
     */
    public function parse(Session $rets, Response $response): array
    {
        /** @var \PHRETS\Parsers\XML $parser */
        $parser = $rets->getConfiguration()->getStrategy()->provide(ParserType::XML);
        $xml = $parser->parse($response);

        $collection = [];

        if ($xml->METADATA) {
            foreach ($xml->METADATA->{'METADATA-LOOKUP'}->Lookup as $key => $value) {
                $metadata = new \PHRETS\Models\Metadata\Lookup();
                $metadata->setSession($rets);
                $this->loadFromXml($metadata, $value, $xml->METADATA->{'METADATA-LOOKUP'});
                $collection[$metadata->getLookupName()] = $metadata;
            }
        }

        return $collection;
    }
}
