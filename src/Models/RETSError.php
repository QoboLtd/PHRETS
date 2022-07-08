<?php

namespace PHRETS\Models;

class RETSError
{
    protected string $code;
    protected string $message;

    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @return $this
     */
    public function setCode(string $code): static
    {
        $this->code = (int) $code;

        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return $this
     */
    public function setMessage(string $message): static
    {
        $this->message = $message;

        return $this;
    }
}
