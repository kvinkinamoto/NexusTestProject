<?php

namespace App\Nexus\Modules\License\Enums;

enum LicenseStatus: string
{
    case ACTIVE = 'active';
    case REVOKED = 'revoked';

    public function label(): string
    {
        return __('license::translate.'.$this->value);
    }
}
