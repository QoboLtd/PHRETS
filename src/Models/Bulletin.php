<?php namespace PHRETS\Models;

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

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param mixed $body
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
     * @return mixed
     */
    public function getDetail($name)
    {
        return Arr::get($this->details, strtoupper((string) $name));
    }

    /**
     * @return mixed
     */
    public function getMemberName()
    {
        return $this->getDetail('MemberName');
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->getDetail('User');
    }

    /**
     * @return mixed
     */
    public function getBroker()
    {
        return $this->getDetail('Broker');
    }

    /**
     * @return mixed
     */
    public function getMetadataVersion()
    {
        return $this->getDetail('MetadataVersion');
    }

    /**
     * @return mixed
     */
    public function getMetadataTimestamp()
    {
        return $this->getDetail('MetadataTimestamp');
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string)$this->body;
    }
}
