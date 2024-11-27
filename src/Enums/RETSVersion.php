<?php
declare(strict_types=1);

namespace PHRETS\Enums;

enum RETSVersion: string
{
    case VERSION_1_5 = '1.5';
    case VERSION_1_7 = '1.7';
    case VERSION_1_7_1 = '1.7.1';
    case VERSION_1_7_2 = '1.7.2';
    case VERSION_1_8 = '1.8';

    public function asHeader(): string
    {
        return 'RETS/' . $this->value;
    }

    public function isAtLeast(self $version): bool
    {
        return version_compare($this->value, $version->value) >= 0;
    }
}
