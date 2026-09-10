<?php

namespace App\Nexus\Modules\Delivery\Requests;

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
            'key' => ['sometimes', 'required', 'string', 'max:64', Rule::unique('deliveries', 'key')->ignore($this->id)],
            'publish' => ['boolean'],
        ];
    }
}
