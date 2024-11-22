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
     */
    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     */
    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }
}
