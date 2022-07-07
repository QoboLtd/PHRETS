<?php

namespace PHRETS\Models;

use Illuminate\Support\Arr;

class Bulletin implements \Stringable
{
    protected $body = null;
    protected $details = [];

    /**
     * @param array $details
     */
    public function __construct($details = [])
    {
        if ($details && is_array($details)) {
            $this->details = array_change_key_case($details, CASE_UPPER);
        }
    }

    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return $this
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    public function setDetail($name, $value)
    {
        $this->details[strtoupper((string) $name)] = $value;

        return $this;
    }

    /**
     * @param $name
     */
    public function getDetail($name)
    {
        return Arr::get($this->details, strtoupper((string) $name));
    }

    public function getMemberName()
    {
        return $this->getDetail('MemberName');
    }

    public function getUser()
    {
        return $this->getDetail('User');
    }

    public function getBroker()
    {
        return $this->getDetail('Broker');
    }

    public function getMetadataVersion()
    {
        return $this->getDetail('MetadataVersion');
    }

    public function getMetadataTimestamp()
    {
        return $this->getDetail('MetadataTimestamp');
    }

    public function __toString(): string
    {
        return (string) $this->body;
    }
}
