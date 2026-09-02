<?php

namespace App\Nexus\Modules\Auth\Requests;

use Illuminate\Validation\Rules\Password;
use Nodex\Nexus\Http\Requests\NexusFormRequest;

class RegisterUserRequest extends NexusFormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => ['required', Password::default()],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => __('auth::translate.name'),
            'email' => __('auth::translate.email'),
            'password' => __('auth::translate.password'),
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('auth::translate.required'),
            'email.required' => __('auth::translate.email_validate.required'),
            'email.email' => __('auth::translate.email_validate.email'),
            'email.unique' => __('auth::translate.email_validate.unique'),
            'password.required' => __('auth::translate.password_validate.required_only'),
            'password.min' => __('auth::translate.password_validate.min'),
            'password.letters' => __('auth::translate.password_validate.letters'),
            'password.mixed' => __('auth::translate.password_validate.mixed'),
            'password.numbers' => __('auth::translate.password_validate.numbers'),
            'password.symbols' => __('auth::translate.password_validate.symbols'),
        ];
    }
}
