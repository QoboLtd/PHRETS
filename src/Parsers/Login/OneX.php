<?php

namespace PHRETS\Parsers\Login;

abstract class OneX
{
    /** @var array<string,bool|int|string> */
    protected array $capabilities = [];

    /** @var array<string,bool|int|string> */
    protected array $details = [];

    /** @var list<string> */
    protected array $valid_transactions = [
        'Action', 'ChangePassword', 'GetObject', 'Login', 'LoginComplete', 'Logout', 'Search', 'GetMetadata',
        'ServerInformation', 'Update', 'PostObject', 'GetPayloadList',
    ];

    public function parse(string $body): void
    {
        $lines = explode("\r\n", $body);
        if (empty($lines[3])) {
            $lines = explode("\n", $body);
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || $line === '0') {
                continue;
            }

            [$name, $value] = $this->readLine($line);
            if ($name) {
                if (in_array($name, $this->valid_transactions) || preg_match('/^X\-/', (string) $name)) {
                    $this->capabilities[$name] = $value;
                } else {
                    $this->details[$name] = $value;
                }
            }
        }
    }

    /**
     * @return array<string,bool|int|string>
     */
    public function getCapabilities(): array
    {
        return $this->capabilities;
    }

    /**
     * @return array<string,bool|int|string>
     */
    public function getDetails(): array
    {
        return $this->details;
    }

    /**
     * @return array{0:string,1:bool|int|string}
     */
    abstract public function readLine(string $line): array;
}
