<?php

namespace PHRETS\Models\Search;

use JsonSerializable;

class Record implements \ArrayAccess, \Stringable, JsonSerializable
{
    protected string $resource = '';
    protected string $class = '';

    /** @var list<string> */
    protected array $fields = [];
    protected ?string $restricted_value = '****';

    /** @var array<int|string,mixed> */
    protected array $values = [];

    public function get(string|int $field): mixed
    {
        return $this->values[$field] ?? null;
    }

    /**
     * @param $value
     */
    public function set(string|int $field, mixed $value): void
    {
        $this->values[$field] = $value;
    }

    public function remove(string|int $field): void
    {
        unset($this->values[$field]);
    }

    public function isRestricted(string|int $field): bool
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

    /**
     * @return list<string>
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @return array<int|string,mixed>
     */
    public function toArray(): array
    {
        return $this->values;
    }

    public function jsonSerialize(): mixed
    {
        return $this->values;
    }

    /**
     * @throws \JsonException
     */
    public function __toString(): string
    {
        return json_encode($this->jsonSerialize(), JSON_THROW_ON_ERROR);
    }

    public function offsetExists(mixed $offset): bool
    {
        assert(is_int($offset) || is_string($offset));
        return array_key_exists($offset, $this->values);
    }

    public function offsetGet(mixed $offset): mixed
    {
        assert(is_int($offset) || is_string($offset));
        return $this->get($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        assert(is_int($offset) || is_string($offset));
        $this->set($offset, $value);
    }

    public function offsetUnset(mixed $offset): void
    {
        assert(is_int($offset) || is_string($offset));
        if (array_key_exists($offset, $this->values)) {
            unset($this->values[$offset]);
        }
    }
}
