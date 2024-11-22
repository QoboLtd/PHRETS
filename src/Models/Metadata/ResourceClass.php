<?php

namespace PHRETS\Models\Metadata;

/**
 * Class ResourceClass.
 *
 * @method string getClassName()
 * @method string getVisibleName()
 * @method string getStandardName()
 * @method string getDescription()
 * @method string getTableVersion()
 * @method string getTableDate()
 * @method string getUpdateVersion()
 * @method string getUpdateDate()
 * @method string getClassTimeStamp()
 * @method string getDeletedFlagField()
 * @method string getDeletedFlagValue()
 * @method string getHasKeyIndex()
 * @method string getVersion()
 * @method string getDate()
 * @method string getResource()
 */
class ResourceClass extends Base
{
    protected array $elements = [
        'ClassName',
        'VisibleName',
        'StandardName',
        'Description',
        'TableVersion',
        'TableDate',
        'UpdateVersion',
        'UpdateDate',
        'ClassTimeStamp',
        'DeletedFlagField',
        'DeletedFlagValue',
        'HasKeyIndex',
    ];
    protected array $attributes = [
        'Version',
        'Date',
        'Resource',
    ];

    /**
     * @return array<string,\PHRETS\Models\Metadata\Table>
     * @throws \PHRETS\Exceptions\CapabilityUnavailable
     */
    public function getTable(): array
    {
        return $this->getSession()->GetTableMetadata($this->getResource(), $this->getClassName());
    }
}
