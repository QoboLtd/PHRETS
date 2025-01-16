<?php
namespace PHRETS\Models\Metadata;

/**
 * Class Resource.
 *
 * @method ?string getResourceID()
 * @method ?string getStandardName()
 * @method ?string getVisibleName()
 * @method ?string getDescription()
 * @method ?string getKeyField()
 * @method ?string getClassCount()
 * @method ?string getClassVersion()
 * @method ?string getClassDate()
 * @method ?string getObjectVersion()
 * @method ?string getObjectDate()
 * @method ?string getSearchHelpVersion()
 * @method ?string getSearchHelpDate()
 * @method ?string getEditMaskVersion()
 * @method ?string getEditMaskDate()
 * @method ?string getLookupVersion()
 * @method ?string getLookupDate()
 * @method ?string getUpdateHelpVersion()
 * @method ?string getUpdateHelpDate()
 * @method ?string getValidationExpressionVersion()
 * @method ?string getValidationExpressionDate()
 * @method ?string getValidationLookupVersion()
 * @method ?string getValidationLookupDate()
 * @method ?string getValidationExternalVersion()
 * @method ?string getValidationExternalDate()
 * @method ?string getVersion()
 * @method ?string getDate()
 * @method void setDescription(?string $description)
 */
class Resource extends Base
{
    protected array $elements = [
        'ResourceID',
        'StandardName',
        'VisibleName',
        'Description',
        'KeyField',
        'ClassCount',
        'ClassVersion',
        'ClassDate',
        'ObjectVersion',
        'ObjectDate',
        'SearchHelpVersion',
        'SearchHelpDate',
        'EditMaskVersion',
        'EditMaskDate',
        'LookupVersion',
        'LookupDate',
        'UpdateHelpVersion',
        'UpdateHelpDate',
        'ValidationExpressionVersion',
        'ValidationExpressionDate',
        'ValidationLookupVersion',
        'ValidationLookupDate',
        'ValidationExternalVersion',
        'ValidationExternalDate',
    ];
    protected array $attributes = [
        'Version',
        'Date',
    ];

    /**
     * @return array<string,\PHRETS\Models\Metadata\ResourceClass>
     *
     * @throws \PHRETS\Exceptions\CapabilityUnavailable
     */
    public function getClasses(): array
    {
        $resourceId = $this->getResourceID();
        assert($resourceId !== null);
        return $this->getSession()->GetClassesMetadata($resourceId);
    }

    /**
     * @return array<string,\PHRETS\Models\Metadata\BaseObject>
     *
     * @throws \PHRETS\Exceptions\CapabilityUnavailable
     */
    public function getObject(): array
    {
        $resourceId = $this->getResourceID();
        assert($resourceId !== null);
        return $this->getSession()->GetObjectMetadata($resourceId);
    }
}
