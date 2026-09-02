<?php

namespace App\Nexus\Modules\Auth\Requests;

use Nodex\Nexus\Http\Requests\NexusFormRequest;

class SendPasswordResetLinkRequest extends NexusFormRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
        ];
    }

    public function attributes(): array
    {
        return [
            'email' => __('auth::translate.email'),
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => __('auth::translate.email_validate.required'),
            'email.email' => __('auth::translate.email_validate.email'),
        ];
    }
}
