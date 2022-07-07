<?php

namespace PHRETS\Models\Search;

class Record implements \ArrayAccess, \Stringable
{
    protected $resource;
    protected $class;
    protected $fields = [];
    protected $restricted_value = '****';
    protected $values = [];

    /**
     * @param $field
     *
     * @return string|null
     */
    public function get($field)
    {
        return $this->values[(string) $field] ?? null;
    }

    /**
     * @param $field
     * @param $value
     */
    public function set($field, $value)
    {
        $this->values[(string) $field] = $value;
    }

    /**
     * @param $field
     */
    public function remove($field)
    {
        unset($this->values[(string) $field]);
    }

    /**
     * @param $field
     *
     * @return bool
     */
    public function isRestricted($field)
    {
        $val = $this->get($field);

        return $val == $this->restricted_value;
    }

    /**
     * @return $this
     */
    public function setParent(Results $results)
    {
        $this->resource = $results->getResource();
        $this->class = $results->getClass();
        $this->restricted_value = $results->getRestrictedIndicator();
        $this->fields = $results->getHeaders();

        return $this;
    }

    /**
     * @return string
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->values;
    }

    /**
     * @return string
     */
    public function toJson()
    {
        return json_encode($this->values, JSON_THROW_ON_ERROR);
    }

    public function __toString(): string
    {
        return $this->toJson();
    }

    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->values);
    }

    /**
     * @return string|null
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->get($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->set($offset, $value);
    }

    public function offsetUnset(mixed $offset): void
    {
        if (array_key_exists($offset, $this->values)) {
            unset($this->values[$offset]);
        }
    }
}
