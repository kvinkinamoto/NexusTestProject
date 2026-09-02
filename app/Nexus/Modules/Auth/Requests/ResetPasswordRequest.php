<?php

namespace App\Nexus\Modules\Auth\Requests;

use Illuminate\Validation\Rules\Password;
use Nodex\Nexus\Http\Requests\NexusFormRequest;

class ResetPasswordRequest extends NexusFormRequest
{
    public function rules(): array
    {
        return [
            'token' => ['required', 'string'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'confirmed', Password::default()],
        ];
    }

    public function attributes(): array
    {
        return [
            'email' => __('auth::translate.email'),
            'password' => __('auth::translate.password'),
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => __('auth::translate.email_validate.required'),
            'email.email' => __('auth::translate.email_validate.email'),
            'password.required' => __('auth::translate.password_validate.required_only'),
            'password.confirmed' => __('auth::translate.password_validate.confirmed'),
            'password.min' => __('auth::translate.password_validate.min'),
            'password.letters' => __('auth::translate.password_validate.letters'),
            'password.mixed' => __('auth::translate.password_validate.mixed'),
            'password.numbers' => __('auth::translate.password_validate.numbers'),
            'password.symbols' => __('auth::translate.password_validate.symbols'),
        ];
    }
}
