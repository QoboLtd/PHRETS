<?php namespace PHRETS\Models\Metadata;

/**
 * Class LookupType
 * @package PHRETS\Models\Metadata
 *
 * @method string getLookupName()
 * @method string getVisibleName()
 * @method string getLookupTypeVersion()
 * @method string getLookupTypeDate()
 * @method string getMetadataEntryID()
 * @method string getVersion()
 * @method string getDate()
 * @method string getResource()
 * @method string getLookup()
 */
class Lookup extends Base
{
    protected array $elements = [
        'MetadataEntryID',
        'LookupName',
        'VisibleName',
        'LookupTypeVersion',
        'LookupTypeDate',
    ];
    protected array $attributes = [
        'Version',
        'Date',
        'Resource',
        'Lookup',
    ];
}
