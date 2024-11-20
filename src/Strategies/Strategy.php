<?php

namespace PHRETS\Strategies;

use PHRETS\Configuration;
use PHRETS\Parsers\ParserType;

interface Strategy
{
    public function provide(ParserType $parser): mixed;
    public function initialize(Configuration $configuration): void;
}
