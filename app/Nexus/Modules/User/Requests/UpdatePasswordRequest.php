<?php

namespace App\Nexus\Modules\User\Requests;

use Illuminate\Validation\Rules\Password;
use Nodex\Nexus\Http\Requests\NexusFormRequest;

class UpdatePasswordRequest extends NexusFormRequest
{
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }

    public function attributes(): array
    {
        return [
            'current_password' => __('user::translate.current_password'),
            'password' => __('user::translate.password'),
            'password_confirmation' => __('user::translate.password_confirmation'),
        ];
    }
}
