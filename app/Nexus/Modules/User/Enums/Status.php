<?php

namespace App\Nexus\Modules\User\Enums;

use App\Models\User;

enum Status: int
{
    case PENDING = 0;
    case ACTIVE = 1;
    case BLOCKED = 2;

    public const DEFAULT = self::ACTIVE;

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'pending',
            self::ACTIVE => 'is_active',
            self::BLOCKED => 'blocked',
        };
    }

    public function type(): string
    {
        return match($this) {
            self::PENDING => 'primary',
            self::ACTIVE => 'success',
            self::BLOCKED => 'danger',
        };
    }

    public static function all(): array
    {
        return array_map(fn($status) => [
            'value' => $status->value,
            'name'  => $status->label(),
            'type'  => $status->type(),
        ], self::cases());
    }

}
