<?php

namespace PHRETS\Models\Metadata;

use PHRETS\Arr;
use PHRETS\Session;

abstract class Base implements \ArrayAccess
{
    protected Session $session;

    /**
     * @var list<string>
     */
    protected array $elements = [];

    /**
     * @var list<string>
     */
    protected array $attributes = [];


    /** @var array<int|string,mixed> */
    protected array $values = [];

    public function getSession(): Session
    {
        return $this->session;
    }

    /**
     * @return $this
     */
    public function setSession(Session $session): static
    {
        $this->session = $session;

        return $this;
    }

    /**
     * @param array<int,mixed> $args Arguments
     */
    public function __call(mixed $name, array $args = []): mixed
    {
        assert(is_string($name));

        $name = strtolower($name);
        $action = substr($name, 0, 3);

        if ($action === 'set') {
            foreach (array_merge($this->getXmlElements(), $this->getXmlAttributes()) as $attr) {
                if (strtolower('set' . $attr) === $name) {
                    $this->values[$attr] = $args[0];
                    break;
                }
            }

            return $this;
        } elseif ($action === 'get') {
            foreach (array_merge($this->getXmlElements(), $this->getXmlAttributes()) as $attr) {
                if (strtolower('get' . $attr) === $name) {
                    return Arr::get($this->values, $attr);
                }
            }

            return null;
        }

        throw new \BadMethodCallException();
    }

    /**
     * @return list<string>
     */
    public function getXmlElements(): array
    {
        return $this->elements;
    }

    /**
     * @return list<string>
     */
    public function getXmlAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Whether a offset exists.
     *
     * @see http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     *
     * @return bool true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists(mixed $offset): bool
    {
        assert(is_string($offset) || is_int($offset));

        foreach (array_merge($this->getXmlElements(), $this->getXmlAttributes()) as $attr) {
            if (strtolower((string) $attr) === strtolower((string) $offset)) {
                return true;
            }
        }

        return false;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve.
     *
     * @see http://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     *
     * @return mixed Can return all value types.
     */
    public function offsetGet(mixed $offset): mixed
    {
        assert(is_string($offset) || is_int($offset));

        foreach (array_merge($this->getXmlElements(), $this->getXmlAttributes()) as $attr) {
            if (strtolower((string) $attr) === strtolower((string) $offset)) {
                return Arr::get($this->values, $attr);
            }
        }

        return null;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to set.
     *
     * @see http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        assert(is_string($offset) || is_int($offset));

        $this->values[$offset] = $value;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset.
     *
     * @see http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     */
    public function offsetUnset(mixed $offset): void
    {
        assert(is_string($offset) || is_int($offset));

        if ($this->offsetExists($offset)) {
            unset($this->values[$offset]);
        }
    }
}
