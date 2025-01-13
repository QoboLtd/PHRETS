<?php

namespace PHRETS\Parsers\Login;

class OneFive extends OneX
{
    /**
     * @inheritDoc
     */
    public function readLine(string $line): array
    {
        $name = null;
        $value = null;

        if (str_contains($line, '=')) {
            @[$name, $value] = explode('=', $line, 2);
        }

        return [trim($name ?? ''), trim($value ?? '')];
    }
}
