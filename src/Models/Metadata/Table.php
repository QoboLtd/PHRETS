<?php

namespace PHRETS\Models\Metadata;

/**
 * Class Table.
 *
 * @method string getSystemName()
 * @method string getStandardName()
 * @method string getLongName()
 * @method string getDBName()
 * @method string getShortName()
 * @method string getMaximumLength()
 * @method string getDataType()
 * @method string getPrecision()
 * @method string getSearchable()
 * @method string getInterpretation()
 * @method string getAlignment()
 * @method string getUseSeparator()
 * @method string getEditMaskID()
 * @method string getLookupName()
 * @method string getMaxSelect()
 * @method string getUnits()
 * @method string getIndex()
 * @method string getMinimum()
 * @method string getMaximum()
 * @method string getDefault()
 * @method string getRequired()
 * @method string getSearchHelpID()
 * @method string getUnique()
 * @method string getMetadataEntryID()
 * @method string getModTimeStamp()
 * @method string getForeignKeyName()
 * @method string getForeignField()
 * @method string getInKeyIndex()
 * @method string getVersion()
 * @method string getDate()
 * @method string getResource()
 * @method string getClass()
 */
class Table extends Base
{
    protected array $elements = [
        'SystemName',
        'StandardName',
        'LongName',
        'DBName',
        'ShortName',
        'MaximumLength',
        'DataType',
        'Precision',
        'Searchable',
        'Interpretation',
        'Alignment',
        'UseSeparator',
        'EditMaskID',
        'LookupName',
        'MaxSelect',
        'Units',
        'Index',
        'Minimum',
        'Maximum',
        'Default',
        'Required',
        'SearchHelpID',
        'Unique',
        'MetadataEntryID',
        'ModTimeStamp',
        'ForeignKeyName',
        'ForeignField',
        'InKeyIndex',
    ];
    protected array $attributes = [
        'Version',
        'Date',
        'Resource',
        'Class',
    ];

    /**
     * @return list<\PHRETS\Models\Metadata\LookupType>
     *
     * @throws \PHRETS\Exceptions\CapabilityUnavailable
     */
    public function getLookupValues(): array
    {
        return $this->session->GetLookupValues($this->getResource(), $this->getLookupName());
    }
}
