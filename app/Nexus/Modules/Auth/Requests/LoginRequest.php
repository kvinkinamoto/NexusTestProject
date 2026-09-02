<?php

namespace App\Nexus\Modules\Auth\Requests;

use Illuminate\Validation\Rule;
use Nodex\Nexus\Http\Requests\NexusFormRequest;

class LoginRequest extends NexusFormRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', Rule::in([0, 1])],
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
        ];
    }
}
