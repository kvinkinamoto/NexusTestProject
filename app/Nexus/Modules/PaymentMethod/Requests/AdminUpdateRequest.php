<?php

namespace App\Nexus\Modules\PaymentMethod\Requests;

use Illuminate\Validation\Rule;
use Nodex\Nexus\Http\Requests\NexusFormRequest;

class AdminUpdateRequest extends NexusFormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'array'],
            'title.*' => ['nullable', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'array'],
            'description.*' => ['nullable', 'string'],
            'code' => ['sometimes', 'required', 'string', 'max:64', Rule::unique('payment_methods', 'code')->ignore($this->id)],
            'provider' => ['sometimes', 'required', 'string', 'max:64'],
            'type' => ['sometimes', 'required', 'string', 'max:64'],
            'count_installment' => ['sometimes', 'nullable', 'integer', 'min:2'],
            'publish' => ['boolean'],
        ];
    }
}
