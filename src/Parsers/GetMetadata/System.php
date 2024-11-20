<?php

namespace PHRETS\Parsers\GetMetadata;

use PHRETS\Http\Response;
use PHRETS\Models\Metadata\System as SystemModel;
use PHRETS\Parsers\XML;
use PHRETS\Session;

class System extends Base
{
    public function parse(Session $rets, Response $response): SystemModel
    {
        /** @var XML $parser */
        $parser = $rets->getConfiguration()->getStrategy()->provide(\PHRETS\Strategies\Strategy::PARSER_XML);
        $xml = $parser->parse($response);

        $base = $xml->METADATA->{'METADATA-SYSTEM'};

        $metadata = new SystemModel();
        $metadata->setSession($rets);

        $configuration = $rets->getConfiguration();

        if ($configuration->getRetsVersion()->is1_5()) {
            if (property_exists($base->System, 'SystemID') && $base->System->SystemID !== null) {
                $metadata->setSystemId((string) $base->System->SystemID);
            }
            if (property_exists($base->System, 'SystemDescription') && $base->System->SystemDescription !== null) {
                $metadata->setSystemDescription((string) $base->System->SystemDescription);
            }
        } else {
            if (property_exists($base->SYSTEM->attributes(), 'SystemID') && $base->SYSTEM->attributes()->SystemID !== null) {
                $metadata->setSystemId((string) $base->SYSTEM->attributes()->SystemID);
            }
            if (property_exists($base->SYSTEM->attributes(), 'SystemDescription') && $base->SYSTEM->attributes()->SystemDescription !== null) {
                $metadata->setSystemDescription((string) $base->SYSTEM->attributes()->SystemDescription);
            }
            if (property_exists($base->SYSTEM->attributes(), 'TimeZoneOffset') && $base->SYSTEM->attributes()->TimeZoneOffset !== null) {
                $metadata->setTimezoneOffset((string) $base->SYSTEM->attributes()->TimeZoneOffset);
            }
        }

        if (property_exists($base->SYSTEM, 'Comments') && $base->SYSTEM->Comments !== null) {
            $metadata->setComments((string) $base->SYSTEM->Comments);
        }
        if (property_exists($base->attributes(), 'Version') && $base->attributes()->Version !== null) {
            $metadata->setVersion((string) $xml->METADATA->{'METADATA-SYSTEM'}->attributes()->Version);
        }

        return $metadata;
    }
}
