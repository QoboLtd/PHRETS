<?php

namespace PHRETS\Parsers\GetMetadata;

use PHRETS\Http\Response;
use PHRETS\Parsers\ParserType;
use PHRETS\Session;

class ResourceClass extends Base
{
    /**
     * @return array<string,\PHRETS\Models\Metadata\ResourceClass>
     */
    public function parse(Session $rets, Response $response): array
    {
        /** @var \PHRETS\Parsers\XML $parser */
        $parser = $rets->getConfiguration()->getStrategy()->provide(ParserType::XML);
        $xml = $parser->parse($response);

        $collection = [];

        if ($xml->METADATA) {
            foreach ($xml->METADATA->{'METADATA-CLASS'}->Class as $key => $value) {
                $metadata = new \PHRETS\Models\Metadata\ResourceClass();
                $metadata->setSession($rets);
                $this->loadFromXml($metadata, $value, $xml->METADATA->{'METADATA-CLASS'});
                $collection[$metadata->getClassName()] = $metadata;
            }
        }

        return $collection;
    }
}
