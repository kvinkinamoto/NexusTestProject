<?php

namespace App\Nexus\Modules\DeliveryType\Requests;

use Nodex\Nexus\Http\Requests\NexusFormRequest;

class AdminUpdateRequest extends NexusFormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'array'],
            'title.*' => ['nullable', 'string', 'max:255'],
            'key' => ['sometimes', 'required', 'string', 'max:64'],
            'publish' => ['boolean'],

            'relation' => ['sometimes', 'array'],
            'relation.delivery' => ['sometimes', 'nullable', 'integer', 'exists:deliveries,id'],
        ];
    }
}
