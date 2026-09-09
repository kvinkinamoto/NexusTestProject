<?php

namespace App\Nexus\Modules\Redirect\Enums;

enum RedirectStatusCode: int
{
    case PERMANENT = 301;
    case TEMPORARY = 302;

    public function label(): string
    {
        return match ($this) {
            self::PERMANENT => '301 - Permanent',
            self::TEMPORARY => '302 - Temporary',
        };
    }
}
