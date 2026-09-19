<?php

namespace App\Nexus\Modules\LicenseActivation\Requests;

use Nodex\Nexus\Http\Requests\NexusFormRequest;

class AdminUpdateRequest extends NexusFormRequest
{
    public function rules(): array
    {
        return [
            'identifier' => ['required', 'string', 'max:255'],
            'activated_at' => ['required', 'date'],

            'relation' => ['nullable', 'array'],
            'relation.license' => ['required', 'integer', 'exists:licenses,id'],
        ];
    }
}
