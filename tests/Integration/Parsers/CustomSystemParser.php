<?php
namespace PHRETS\Test\Integration\Parsers;

use PHRETS\Http\Response;
use PHRETS\Models\Metadata\System as SystemModel;
use PHRETS\Parsers\GetMetadata\System;
use PHRETS\Session;

class CustomSystemParser extends System
{
    public function parse(Session $rets, Response $response): SystemModel
    {
        $metadata = new SystemModel();

        $metadata->setSession($rets);
        $metadata->setSystemID('custom');

        return $metadata;
    }
}
