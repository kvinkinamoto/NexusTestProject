<?php

namespace App\Nexus\Modules\OrderStatus\Requests;

use Nodex\Nexus\Http\Requests\NexusFormRequest;

class AdminStoreRequest extends NexusFormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'array'],
            'name.*' => ['nullable', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'is_default' => ['boolean'],
            'publish' => ['boolean'],
        ];
    }
}
