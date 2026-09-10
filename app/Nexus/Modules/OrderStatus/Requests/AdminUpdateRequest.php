<?php

namespace App\Nexus\Modules\OrderStatus\Requests;

use Nodex\Nexus\Http\Requests\NexusFormRequest;

class AdminUpdateRequest extends NexusFormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'array'],
            'name.*' => ['nullable', 'string', 'max:255'],
            'slug' => ['sometimes', 'nullable', 'string', 'max:255'],
            'is_default' => ['boolean'],
            'publish' => ['boolean'],
        ];
    }
}
