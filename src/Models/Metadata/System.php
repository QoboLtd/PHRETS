<?php

namespace PHRETS\Models\Metadata;

use Illuminate\Support\Collection;
use PHRETS\Exceptions\CapabilityUnavailable;
use PHRETS\Exceptions\MetadataNotFound;

/**
 * Class System.
 *
 * @method string getSystemID()
 * @method string getSystemDescription()
 * @method string getTimeZoneOffset()
 * @method string getComments()
 * @method string getVersion()
 * @method void setSystemID(string $systemID)
 * @method void setSystemDescription(string $systemDescription)
 * @method void setTimeZoneOffset(string $timeZoneOffset)
 * @method void setComments(string $comments)
 * @method void setVersion(string $version)
 */
class System extends Base
{
    protected array $elements = [
        'SystemID',
        'SystemDescription',
        'TimeZoneOffset',
        'Comments',
        'Version',
    ];

    /**
     * @return \Illuminate\Support\Collection|\PHRETS\Models\Metadata\Resource[]
     *
     * @throws MetadataNotFound|CapabilityUnavailable
     */
    public function getResources(): Collection|array
    {
        return $this->getSession()->GetResourcesMetadata();
    }
}
