<?php

namespace PHRETS\Parsers\GetMetadata;

use PHRETS\Http\Response;
use PHRETS\Parsers\ParserType;
use PHRETS\Session;

class Table extends Base
{
    /**
     * @return array<string,\PHRETS\Models\Metadata\Table>
     */
    public function parse(Session $rets, Response $response, string $keyed_by): array
    {
        /** @var \PHRETS\Parsers\XML $parser */
        $parser = $rets->getConfiguration()->getStrategy()->provide(ParserType::XML);
        $xml = $parser->parse($response);

        $parts = [];
        if ($xml->METADATA) {
            foreach ($xml->METADATA->{'METADATA-TABLE'}->Field as $key => $value) {
                $metadata = new \PHRETS\Models\Metadata\Table();
                $metadata->setSession($rets);
                $this->loadFromXml($metadata, $value, $xml->METADATA->{'METADATA-TABLE'});
                $method = 'get' . $keyed_by;
                $parts[(string) $metadata->$method()] = $metadata;
            }
        }

        return $parts;
    }
}
