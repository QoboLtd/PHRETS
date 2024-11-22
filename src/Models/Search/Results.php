<?php

namespace PHRETS\Models\Search;

use ArrayAccess;
use ArrayIterator;
use Closure;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use League\Csv\Writer;
use PHRETS\Arr;
use PHRETS\Session;
use SplTempFileObject;
use Traversable;

class Results implements Countable, ArrayAccess, IteratorAggregate, JsonSerializable
{
    protected string $resource = '';
    protected string $class = '';
    protected ?Session $session = null;

    /** @var array<int|string,mixed> */
    protected ?array $metadata = null;
    protected int $total_results_count = 0;
    protected int $returned_results_count = 0;
    protected mixed $error = null;
    /** @var array<int|string,\PHRETS\Models\Search\Record> */
    protected array $results;

    /** @var list<string> */
    protected array $headers = [];
    protected string $restricted_indicator = '****';
    protected bool $maxrows_reached = false;

    public function __construct()
    {
        $this->results = [];
    }

    /**
     * @return list<string>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @param list<string> $headers Headers
     * @return self
     */
    public function setHeaders(array $headers): self
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * @param callable|string|int|null $keyed_by
     */
    public function addRecord(Record $record, callable|string|int|null $keyed_by = null): void
    {
        // register this Results object as the record's parent automatically
        $record->setParent($this);

        $this->returned_results_count++;

        if (is_callable($keyed_by)) {
            $this->results[$keyed_by($record)] = $record;
        } elseif ($keyed_by) {
            $this->results[$record->get($keyed_by)] = $record;
        } else {
            $this->results[] = $record;
        }
    }

    /**
     * Set which field's value will be used to key the records by.
     *
     */
    public function keyResultsBy(callable|string|int|null $field): void
    {
        $results = $this->results;
        $this->results = [];
        foreach ($results as $r) {
            $this->addRecord($r, $field);
        }
    }

    /**
     * Grab a record by it's tracked key.
     *
     * @param string|int $keyId
     */
    public function find(string|int $keyId): ?Record
    {
        return $this->results[$keyId] ?? null;
    }

    /**
     * @return mixed
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @param mixed $error
     *
     * @return self
     */
    public function setError(mixed $error): self
    {
        $this->error = $error;

        return $this;
    }

    public function getReturnedResultsCount(): int
    {
        return $this->returned_results_count;
    }

    /**
     * @return self
     */
    public function setReturnedResultsCount(int $returned_results_count): self
    {
        $this->returned_results_count = $returned_results_count;

        return $this;
    }

    public function getTotalResultsCount(): int
    {
        return $this->total_results_count;
    }

    /**
     * @return self
     */
    public function setTotalResultsCount(int $total_results_count): self
    {
        $this->total_results_count = $total_results_count;

        return $this;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function setClass(string $class): self
    {
        $this->class = $class;

        return $this;
    }

    public function getResource(): string
    {
        return $this->resource;
    }

    /**
     * @return self
     */
    public function setResource(string $resource): self
    {
        $this->resource = $resource;

        return $this;
    }

    /**
     * @return \PHRETS\Session
     */
    public function getSession(): ?Session
    {
        return $this->session;
    }

    /**
     * @return self
     */
    public function setSession(Session $session): self
    {
        $this->session = $session;

        return $this;
    }

    /**
     * @return ?array<int|string,mixed>
     * @throws \PHRETS\Exceptions\CapabilityUnavailable
     */
    public function getMetadata(): ?array
    {
        if ($this->metadata === null && $this->session !== null) {
            $this->metadata = $this->session->GetTableMetadata($this->getResource(), $this->getClass());
        }

        return $this->metadata;
    }

    /**
     * @param array<int|string,mixed> $metadata
     *
     * @return self
     */
    public function setMetadata(array $metadata): self
    {
        $this->metadata = $metadata;

        return $this;
    }

    public function getRestrictedIndicator(): string
    {
        return $this->restricted_indicator;
    }

    /**
     * @param string $indicator
     *
     * @return self
     */
    public function setRestrictedIndicator(string $indicator): self
    {
        $this->restricted_indicator = $indicator;

        return $this;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->results);
    }

    public function offsetExists(mixed $offset): bool
    {
        assert(is_string($offset) || is_int($offset));
        return array_key_exists($offset, $this->results);
    }

    public function offsetGet(mixed $offset): mixed
    {
        assert(is_string($offset) || is_int($offset));
        return $this->results[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        assert($value instanceof Record);
        assert($offset === null || is_string($offset) || is_int($offset));
        if ($offset) {
            $this->addRecord($value, fn () => $offset);
        } else {
            $this->addRecord($value);
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->results[$offset]);
    }

    public function count(): int
    {
        return count($this->results);
    }

    /**
     * @param ?\PHRETS\Models\Search\Record $default
     */
    public function first(Closure $callback = null, ?Record $default = null): ?Record
    {
        foreach ($this->results as $record) {
            if ($callback === null || $callback($record)) {
                return $record;
            }
        }

        return $default;
    }

    public function last(): ?Record
    {
        return Arr::last($this->results);
    }

    public function isMaxRowsReached(): bool
    {
        return $this->maxrows_reached == true;
    }

    /**
     * @return $this
     */
    public function setMaxRowsReached(bool $boolean = true): static
    {
        $this->maxrows_reached = $boolean;

        return $this;
    }

    /**
     * Returns an array containing the values from the given field.
     *
     * @param string|int $field
     * @return list<mixed>
     */
    public function lists(string|int $field): array
    {
        $l = [];
        foreach ($this->results as $r) {
            $v = $r->get($field);
            if ($v && !$r->isRestricted($field)) {
                $l[] = $v;
            }
        }

        return $l;
    }

    public function jsonSerialize(): mixed
    {
        return $this->results;
    }

    /**
     * Return results as a simple array.
     * @return list<array<int|string,mixed>>
     */
    public function toArray(): array
    {
        $result = [];
        foreach ($this->results as $r) {
            $result[] = $r->toArray();
        }

        return $result;
    }
}
