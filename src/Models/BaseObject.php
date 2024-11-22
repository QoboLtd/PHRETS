<?php

namespace PHRETS\Models;

class BaseObject
{
    protected ?string $content_type = null;
    protected ?string $content_id = null;
    protected string|int|null $object_id = null;
    protected ?string $mime_version = null;
    protected ?string $location = null;
    protected ?string $content_description = null;
    protected ?string $content_sub_description = null;
    protected ?string $content = null;
    protected int|string|null $preferred = null;
    protected ?RETSError $error = null;

    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * @return self
     */
    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getContentDescription(): ?string
    {
        return $this->content_description;
    }

    /**
     * @return $this
     */
    public function setContentDescription(?string $content_description): static
    {
        $this->content_description = $content_description;

        return $this;
    }

    public function getContentId(): ?string
    {
        return $this->content_id;
    }

    /**
     * @return self
     */
    public function setContentId(?string $content_id): self
    {
        $this->content_id = $content_id;

        return $this;
    }

    public function getContentSubDescription(): ?string
    {
        return $this->content_sub_description;
    }

    /**
     * @return self
     */
    public function setContentSubDescription(?string $content_sub_description): self
    {
        $this->content_sub_description = $content_sub_description;

        return $this;
    }

    public function getContentType(): ?string
    {
        return $this->content_type;
    }

    /**
     * @return self
     */
    public function setContentType(?string $content_type): self
    {
        $this->content_type = $content_type;

        return $this;
    }

    public function getLocation():? string
    {
        return $this->location;
    }

    /**
     * @return self
     */
    public function setLocation(?string $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getMimeVersion(): ?string
    {
        return $this->mime_version;
    }

    /**
     * @return self
     */
    public function setMimeVersion(?string $mime_version): self
    {
        $this->mime_version = $mime_version;

        return $this;
    }

    public function getObjectId(): string|int|null
    {
        return $this->object_id;
    }

    /**
     * @return self
     */
    public function setObjectId(string|int|null $object_id): self
    {
        $this->object_id = $object_id;

        return $this;
    }

    /**
     * @param ?string $name Name
     * @param string$value Value
     */
    public function setFromHeader(?string $name, string $value): void
    {
        $headers = [
            'Content-Description' => 'ContentDescription',
            'Content-Sub-Description' => 'ContentSubDescription',
            'Content-ID' => 'ContentId',
            'Object-ID' => 'ObjectId',
            'Location' => 'Location',
            'Content-Type' => 'ContentType',
            'MIME-Version' => 'MimeVersion',
            'Preferred' => 'Preferred',
        ];

        $headers = array_change_key_case($headers, CASE_UPPER);

        if (array_key_exists(strtoupper($name), $headers)) {
            $method = 'set' . $headers[strtoupper($name)];
            $this->$method($value);
        }
    }

    public function getSize(): int
    {
        return strlen((string)$this->getContent());
    }

    public function getPreferred(): int|string|null
    {
        return $this->preferred;
    }

    /**
     * Check whether or not this object is marked as Preferred (primary).
     */
    public function isPreferred(): bool
    {
        return $this->getPreferred() == '1';
    }

    /**
     * @return self
     */
    public function setPreferred(int|string|null $preferred): self
    {
        $this->preferred = $preferred;

        return $this;
    }

    public function getError(): ?RETSError
    {
        return $this->error;
    }

    /**
     * @return $this
     */
    public function setError(?RETSError $error): static
    {
        $this->error = $error;

        return $this;
    }

    public function isError(): bool
    {
        return $this->error !== null;
    }
}
