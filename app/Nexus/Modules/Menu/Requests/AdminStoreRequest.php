<?php

namespace App\Nexus\Modules\Menu\Requests;

use Nodex\Nexus\Http\Requests\NexusFormRequest;

class AdminStoreRequest extends NexusFormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'key' => 'nullable|string|alpha_dash|unique:menus,key',
            'is_active' => 'nullable|boolean',
        ];
    }
}
