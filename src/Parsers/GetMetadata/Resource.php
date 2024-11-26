<?php

namespace PHRETS\Parsers\GetMetadata;

use PHRETS\Http\Response;
use PHRETS\Parsers\ParserType;
use PHRETS\Session;

class Resource extends Base
{
    /**
     * @return array<string,\PHRETS\Models\Metadata\Resource>
     */
    public function parse(Session $rets, Response $response): array
    {
        /** @var \PHRETS\Parsers\XML $parser */
        $parser = $rets->getConfiguration()->getStrategy()->provide(ParserType::XML);
        $xml = $parser->parse($response);

        $collection = [];

        if ($xml->METADATA) {
            foreach ($xml->METADATA->{'METADATA-RESOURCE'}->Resource as $key => $value) {
                $metadata = new \PHRETS\Models\Metadata\Resource();
                $metadata->setSession($rets);
                $this->loadFromXml($metadata, $value, $xml->METADATA->{'METADATA-RESOURCE'});

                $resourceId = $metadata->getResourceID();
                if ($resourceId === null) {
                    continue;
                }

                $collection[$resourceId] = $metadata;
            }
        }

        return $collection;
    }
}
