<?php

namespace PHRETS\Parsers\Login;

class OneEight extends OneX
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

        $value = trim($value ?? '');

        if ($name == 'Info' && $value) {
            // break it up on the 2 required parts
            [$info_token_name, $info_token_value] = explode(';', $value, 2);
            $info_token_type = null;
            // see if the optional 3rd part was given
            if (str_contains($info_token_value, ';')) {
                // they included the optional type
                [$info_token_type, $info_token_value] = explode(';', $info_token_value);

                if ($info_token_type == 'Int') {
                    $info_token_value = (int) $info_token_value;
                } elseif ($info_token_type == 'Boolean') {
                    $info_token_value = (bool) $info_token_value;
                } else {
                    $info_token_value = trim($info_token_value);
                }
            } else {
                $info_token_value = trim($info_token_value);
            }
            $name = $info_token_name;
            $value = $info_token_value;
        }

        return [trim($name ?? ''), $value];
    }
}
