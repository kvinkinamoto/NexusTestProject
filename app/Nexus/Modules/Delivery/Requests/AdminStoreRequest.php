<?php

namespace App\Nexus\Modules\Delivery\Requests;

use Illuminate\Validation\Rule;
use Nodex\Nexus\Http\Requests\NexusFormRequest;

class AdminStoreRequest extends NexusFormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['required', 'array'],
            'title.*' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'array'],
            'description.*' => ['nullable', 'string'],
            'key' => ['required', 'string', 'max:64', Rule::unique('deliveries', 'key')],
            'publish' => ['boolean'],
        ];
    }
}
