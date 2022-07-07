<?php

namespace PHRETS\Models\Metadata;

/**
 * Class LookupType.
 *
 * @method string getValue
 * @method string getLongValue
 * @method string getShortValue
 * @method string getMetadataEntryID
 * @method string getVersion
 * @method string getDate
 * @method string getResource
 * @method string getLookup
 */
class LookupType extends Base
{
    protected $elements = [
        'MetadataEntryID',
        'LongValue',
        'ShortValue',
        'Value',
    ];
    protected $attributes = [
        'Version',
        'Date',
        'Resource',
        'Lookup',
    ];
}
