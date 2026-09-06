<?php

namespace App\Nexus\Modules\Plugins\Requests;

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
