<?php

namespace PHRETS\Parsers\GetMetadata;

use PHRETS\Models\Metadata\Base as BaseModel;
use SimpleXMLElement;

class Base
{
    protected function loadFromXml(BaseModel $model, SimpleXMLElement $xml, ?SimpleXMLElement $attributes = null): void
    {
        foreach ($model->getXmlAttributes() as $attr) {
            if (isset($attributes[$attr])) {
                $method = 'set' . $attr;
                $model->$method((string) $attributes[$attr]);
            }
        }

        foreach ($model->getXmlElements() as $attr) {
            if (isset($xml->$attr)) {
                $method = 'set' . $attr;
                $model->$method((string) $xml->$attr);
            }
        }
    }
}
