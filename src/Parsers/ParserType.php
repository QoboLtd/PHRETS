<?php
namespace PHRETS\Parsers;

enum ParserType : string
{
    case LOGIN = 'parser.login';
    case OBJECT_SINGLE = 'parser.object.single';
    case OBJECT_MULTIPLE = 'parser.object.multiple';
    case SEARCH = 'parser.search';
    case SEARCH_RECURSIVE = 'parser.search.recursive';
    case METADATA_SYSTEM = 'parser.metadata.system';
    case METADATA_RESOURCE = 'parser.metadata.resource';
    case METADATA_CLASS = 'parser.metadata.class';
    case METADATA_TABLE = 'parser.metadata.table';
    case METADATA_OBJECT = 'parser.metadata.object';
    case METADATA_LOOKUP = 'parser.metadata.lookup';
    case METADATA_LOOKUPTYPE = 'parser.metadata.lookuptype';
    case UPDATE = 'parser.update';
    case OBJECT_POST = 'parser.object.post';
    case XML = 'parser.xml';
}
