<?php

namespace App\Nexus\Modules\Settings\Requests;

use Nodex\Nexus\Http\Requests\NexusFormRequest;

class AdminUpdateRequest extends NexusFormRequest
{
    public function rules(): array
    {
        return [
            'site_name' => 'required|string',
            'site_description' => 'nullable|string',
            'maintenance_mode' => 'nullable|boolean',
            'logo' => 'nullable|string',
            'favicon' => 'nullable|string',
        ];
    }
}
