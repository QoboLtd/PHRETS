<?php

namespace PHRETS\Models;

use Illuminate\Support\Arr;

class Bulletin implements \Stringable
{
    protected ?string $body = null;
    protected array $details = [];

    public function __construct(array $details = [])
    {
        if ($details && is_array($details)) {
            $this->details = array_change_key_case($details, CASE_UPPER);
        }
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    /**
     * @return $this
     */
    public function setBody($body): static
    {
        $this->body = $body;

        return $this;
    }

    public function setDetail(string $name, $value): static
    {
        $this->details[strtoupper($name)] = $value;

        return $this;
    }

    public function getDetail(string $name): mixed
    {
        return Arr::get($this->details, strtoupper($name));
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
