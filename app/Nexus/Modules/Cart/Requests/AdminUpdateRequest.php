<?php

namespace App\Nexus\Modules\Cart\Requests;

use Nodex\Nexus\Http\Requests\NexusFormRequest;

class AdminUpdateRequest extends NexusFormRequest
{
    public function rules(): array
    {
        return [
            'relation' => ['nullable', 'array'],
            'relation.user' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }
}
