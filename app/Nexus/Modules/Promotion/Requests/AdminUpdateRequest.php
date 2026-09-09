<?php

namespace App\Nexus\Modules\Promotion\Requests;

use Nodex\Nexus\Http\Requests\NexusFormRequest;

class AdminUpdateRequest extends NexusFormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'array'],
            'name.*' => ['nullable', 'string', 'max:255'],
            'label' => ['nullable', 'array'],
            'label.*' => ['nullable', 'string', 'max:255'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after:starts_at'],
            'publish' => ['boolean'],

            'relation' => ['nullable', 'array'],
            'relation.discountRule' => ['required', 'integer', 'exists:discount_rules,id'],
        ];
    }
}
