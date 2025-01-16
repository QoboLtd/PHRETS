<?php

namespace PHRETS\Models;

use PHRETS\Arr;

class Bulletin implements \Stringable
{
    protected ?string $body = null;

    /** @var array<string,bool|int|string> */
    protected array $details = [];

    /**
     * @param array<string,bool|int|string> $details
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
     */
    public function setBody(?string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function setDetail(string $name, string $value): self
    {
        $this->details[strtoupper($name)] = $value;

        return $this;
    }

    public function getDetail(string $name): ?string
    {
        /** @var bool|int|string|null $value */
        $value = Arr::get($this->details, strtoupper($name));
        if ($value === null) {
            return null;
        }

        return (string)$value;
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
