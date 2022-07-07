<?php

namespace PHRETS\Models;

class BaseObject
{
    protected $content_type;
    protected $content_id;
    protected $object_id;
    protected $mime_version;
    protected $location;
    protected $content_description;
    protected $content_sub_description;
    protected $content;
    protected $preferred;
    protected $error;

    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    public function getContentDescription()
    {
        return $this->content_description;
    }

    /**
     * @return $this
     */
    public function setContentDescription($content_description)
    {
        $this->content_description = $content_description;

        return $this;
    }

    public function getContentId()
    {
        return $this->content_id;
    }

    /**
     * @return $this
     */
    public function setContentId($content_id)
    {
        $this->content_id = $content_id;

        return $this;
    }

    public function getContentSubDescription()
    {
        return $this->content_sub_description;
    }

    /**
     * @return $this
     */
    public function setContentSubDescription($content_sub_description)
    {
        $this->content_sub_description = $content_sub_description;

        return $this;
    }

    public function getContentType()
    {
        return $this->content_type;
    }

    /**
     * @return $this
     */
    public function setContentType($content_type)
    {
        $this->content_type = $content_type;

        return $this;
    }

    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @return $this
     */
    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }

    public function getMimeVersion()
    {
        return $this->mime_version;
    }

    /**
     * @return $this
     */
    public function setMimeVersion($mime_version)
    {
        $this->mime_version = $mime_version;

        return $this;
    }

    public function getObjectId()
    {
        return $this->object_id;
    }

    /**
     * @return $this
     */
    public function setObjectId($object_id)
    {
        $this->object_id = $object_id;

        return $this;
    }

    /**
     * @param $name
     * @param $value
     */
    public function setFromHeader($name, $value)
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

        if (array_key_exists(strtoupper((string) $name), $headers)) {
            $method = 'set' . $headers[strtoupper((string) $name)];
            $this->$method($value);
        }
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return strlen((string) $this->getContent());
    }

    public function getPreferred()
    {
        return $this->preferred;
    }

    /**
     * Check whether or not this object is marked as Preferred (primary).
     *
     * @return bool
     */
    public function isPreferred()
    {
        return $this->getPreferred() == '1';
    }

    /**
     * @return $this
     */
    public function setPreferred($preferred)
    {
        $this->preferred = $preferred;

        return $this;
    }

    /**
     * @return \PHRETS\Models\RETSError
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @return $this
     */
    public function setError(RETSError $error)
    {
        $this->error = $error;

        return $this;
    }

    /**
     * @return bool
     */
    public function isError()
    {
        return $this->error !== null;
    }
}
