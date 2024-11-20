<?php

namespace PHRETS\Strategies;

use PHRETS\Configuration;

interface Strategy
{
    public const PARSER_LOGIN = 'parser.login';
    public const PARSER_OBJECT_SINGLE = 'parser.object.single';
    public const PARSER_OBJECT_MULTIPLE = 'parser.object.multiple';
    public const PARSER_SEARCH = 'parser.search';
    public const PARSER_SEARCH_RECURSIVE = 'parser.search.recursive';
    public const PARSER_METADATA_SYSTEM = 'parser.metadata.system';
    public const PARSER_METADATA_RESOURCE = 'parser.metadata.resource';
    public const PARSER_METADATA_CLASS = 'parser.metadata.class';
    public const PARSER_METADATA_TABLE = 'parser.metadata.table';
    public const PARSER_METADATA_OBJECT = 'parser.metadata.object';
    public const PARSER_METADATA_LOOKUPTYPE = 'parser.metadata.lookuptype';
    public const PARSER_XML = 'parser.xml';

    /**
     * @param string $component
     */
    public function provide(string $component);

    public function initialize(Configuration $configuration);
}
