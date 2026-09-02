<?php

namespace App\Nexus\Modules\User\Requests;

use Nodex\Nexus\Http\Requests\NexusFormRequest;

class StoreUserAddressRequest extends NexusFormRequest
{
    public function rules(): array
    {
        return [
            'city' => ['required', 'string', 'max:255'],
            'street' => ['required', 'string', 'max:255'],
            'house' => ['required', 'string', 'max:32'],
            'apartment' => ['nullable', 'string', 'max:32'],
            'is_main' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'city' => __('user::translate.city'),
            'street' => __('user::translate.street'),
            'house' => __('user::translate.house'),
            'apartment' => __('user::translate.apartment'),
            'is_main' => __('user::translate.is_main'),
        ];
    }
}
