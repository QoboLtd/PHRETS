<?php

namespace PHRETS\Models;

use Illuminate\Support\Arr;

class Bulletin implements \Stringable
{
    protected ?string $body = null;

    /** @var array<string,mixed> */
    protected array $details = [];

    public function __construct(array $details = [])
    {
        $this->details = array_change_key_case($details, CASE_UPPER);
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    /**
     * @param ?string $body Body
     * @return self
     */
    public function setBody(?string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function setDetail(string $name, $value): self
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
        return $this->body ?? '';
    }
}
