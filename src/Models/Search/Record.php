<?php

namespace PHRETS\Models\Search;

use JsonException;

class Record implements \ArrayAccess, \Stringable
{
    protected ?string $resource = '';
    protected ?string $class = '';
    protected array $fields = [];
    protected ?string $restricted_value = '****';
    protected array $values = [];

    public function get(string $field): mixed
    {
        return $this->values[$field] ?? null;
    }

    /**
     * @param $value
     */
    public function set(string $field, $value)
    {
        $this->values[$field] = $value;
    }

    public function remove(string $field)
    {
        unset($this->values[$field]);
    }

    public function isRestricted(string $field): bool
    {
        $val = $this->get($field);

        return $val === $this->restricted_value;
    }

    /**
     * @return $this
     */
    public function setParent(Results $results): static
    {
        $this->resource = $results->getResource();
        $this->class = $results->getClass();
        $this->restricted_value = $results->getRestrictedIndicator();
        $this->fields = $results->getHeaders();

        return $this;
    }

    public function getResource(): string
    {
        return $this->resource;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function toArray(): array
    {
        return $this->values;
    }

    /**
     * @throws JsonException
     */
    public function toJson(): string
    {
        return json_encode($this->values, JSON_THROW_ON_ERROR);
    }

    /**
     * @throws JsonException
     */
    public function __toString(): string
    {
        return $this->toJson();
    }

    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->values);
    }

    public function offsetGet(mixed $offset): ?string
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
