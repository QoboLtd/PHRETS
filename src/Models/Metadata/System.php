<?php

namespace PHRETS\Models\Metadata;

use Illuminate\Support\Collection;
use PHRETS\Exceptions\CapabilityUnavailable;
use PHRETS\Exceptions\MetadataNotFound;

/**
 * Class System.
 *
 * @method string getSystemID
 * @method string getSystemDescription
 * @method string getTimeZoneOffset
 * @method string getComments
 * @method string getVersion
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
     * @return Collection|resource[]
     *
     * @throws MetadataNotFound|CapabilityUnavailable
     */
    public function getResources(): Collection|array
    {
        return $this->getSession()->GetResourcesMetadata();
    }
}
