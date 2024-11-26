<?php

namespace PHRETS\Parsers\GetMetadata;

use PHRETS\Http\Response;
use PHRETS\Parsers\ParserType;
use PHRETS\Session;

class BaseObject extends Base
{
    /**
     * @return array<string,\PHRETS\Models\Metadata\BaseObject>
     */
    public function parse(Session $rets, Response $response): array
    {
        /** @var \PHRETS\Parsers\XML $parser */
        $parser = $rets->getConfiguration()->getStrategy()->provide(ParserType::XML);
        $xml = $parser->parse($response);

        $collection = [];

        if ($xml->METADATA && $xml->METADATA->{'METADATA-OBJECT'}) {
            foreach ($xml->METADATA->{'METADATA-OBJECT'}->Object as $key => $value) {
                $metadata = new \PHRETS\Models\Metadata\BaseObject();
                $metadata->setSession($rets);
                $this->loadFromXml($metadata, $value, $xml->METADATA->{'METADATA-OBJECT'});
                $collection[$metadata->getObjectType()] = $metadata;
            }
        }

        return $collection;
    }
}
