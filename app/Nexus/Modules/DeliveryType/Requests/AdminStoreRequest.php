<?php

namespace App\Nexus\Modules\DeliveryType\Requests;

use Nodex\Nexus\Http\Requests\NexusFormRequest;

class AdminStoreRequest extends NexusFormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['required', 'array'],
            'title.*' => ['nullable', 'string', 'max:255'],
            'key' => ['required', 'string', 'max:64'],
            'publish' => ['boolean'],

            'relation' => ['nullable', 'array'],
            'relation.delivery' => ['nullable', 'integer', 'exists:deliveries,id'],
        ];
    }
}
