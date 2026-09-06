<?php

namespace App\Nexus\Modules\Modules\Requests;

use Nodex\Nexus\Http\Requests\NexusFormRequest;

class AdminUpdateRequest extends NexusFormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'nullable|string',
        ];
    }
}
