<?php

namespace PHRETS\Versions;

use PHRETS\Exceptions\InvalidRETSVersion;

class RETSVersion implements \Stringable
{
    public const VERSION_1_5 = '1.5';
    public const VERSION_1_7 = '1.7';
    public const VERSION_1_7_1 = '1.7.1';
    public const VERSION_1_7_2 = '1.7.2';
    public const VERSION_1_8 = '1.8';

    protected string $number;

    private const VALID_VERSIONS = [
        self::VERSION_1_5,
        self::VERSION_1_7,
        self::VERSION_1_7_1,
        self::VERSION_1_7_2,
        self::VERSION_1_8,
    ];

    /**
     * @param string $version
     * @return self
     *
     * @throws \PHRETS\Exceptions\InvalidRETSVersion
     */
    public function setVersion(string $version): self
    {
        $this->number = str_replace('RETS/', '', $version);
        if (!in_array($this->number, self::VALID_VERSIONS)) {
            throw new InvalidRETSVersion("RETS version '{$version}' given is not understood");
        }

        return $this;
    }

    public function getVersion(): string
    {
        return $this->number;
    }

    public function asHeader(): string
    {
        return 'RETS/' . $this->number;
    }

    public function is1_5(): bool
    {
        return $this->number == self::VERSION_1_5;
    }

    public function is1_7(): bool
    {
        return $this->number == self::VERSION_1_7;
    }

    public function is1_7_2(): bool
    {
        return $this->number == self::VERSION_1_7_2;
    }

    public function is1_8(): bool
    {
        return $this->number == self::VERSION_1_8;
    }

    /**
     * @param string $version
     */
    public function isAtLeast(string $version): bool
    {
        return version_compare($this->number, $version) >= 0;
    }

    public function isAtLeast1_5(): bool
    {
        return $this->isAtLeast(self::VERSION_1_5);
    }

    public function isAtLeast1_7(): bool
    {
        return $this->isAtLeast(self::VERSION_1_7);
    }

    public function isAtLeast1_7_2(): bool
    {
        return $this->isAtLeast(self::VERSION_1_7_2);
    }

    public function isAtLeast1_8(): bool
    {
        return $this->isAtLeast(self::VERSION_1_8);
    }

    /**
     * @return list<string>
     */
    public function getValidVersions(): array
    {
        return self::VALID_VERSIONS;
    }

    public function __toString(): string
    {
        return $this->asHeader();
    }
}
