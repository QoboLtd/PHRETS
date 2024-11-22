<?php

namespace PHRETS\Models;

use PHRETS\Arr;

class Bulletin implements \Stringable
{
    protected ?string $body = null;

    /** @var array<string,string> */
    protected array $details = [];

    /**
     * @param array<string,string> $details
     */
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

    public function setDetail(string $name, mixed $value): self
    {
        $this->details[strtoupper($name)] = $value;

        return $this;
    }

    public function getDetail(string $name): ?string
    {
        return Arr::get($this->details, strtoupper($name));
    }

    public function getMemberName(): ?string
    {
        return $this->getDetail('MemberName');
    }

    public function getUser(): ?string
    {
        return $this->getDetail('User');
    }

    public function getBroker(): ?string
    {
        return $this->getDetail('Broker');
    }

    public function getMetadataVersion(): ?string
    {
        return $this->getDetail('MetadataVersion');
    }

    public function getMetadataTimestamp(): ?string
    {
        return $this->getDetail('MetadataTimestamp');
    }

    public function __toString(): string
    {
        return $this->body ?? '';
    }
}
