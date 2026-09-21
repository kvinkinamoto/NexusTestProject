<?php

namespace App\Nexus\Modules\Order\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAccountOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_name' => ['required', 'string', 'max:255'],
            'user_last_name' => ['nullable', 'string', 'max:255'],
            'user_phone' => ['required', 'string', 'max:32'],
            'user_email' => ['nullable', 'email', 'max:255'],
            'user_comment' => ['nullable', 'string'],
            'needs_phone_call' => ['boolean'],
        ];
    }
}
