<?php

namespace App\Nexus\Modules\User\Enums;

enum Gender: string
{
    case MALE = 'male';
    case FEMALE = 'female';

    public function label(): string
    {
        return trans('user::translate.enum_genders.' . $this->name);
    }

    public static function all(): array
    {
        return array_map(fn (self $case) => [
            'value' => $case->value,
            'label' => $case->label(),
        ], self::cases());
    }
}
