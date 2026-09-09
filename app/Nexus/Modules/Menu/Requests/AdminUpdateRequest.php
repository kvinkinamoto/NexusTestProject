<?php

namespace App\Nexus\Modules\Menu\Requests;

use Illuminate\Validation\Rule;
use Nodex\Nexus\Http\Requests\NexusFormRequest;

class AdminUpdateRequest extends NexusFormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'key' => ['nullable', 'string', 'alpha_dash', Rule::unique('menus', 'key')->ignore($this->id)],
            'is_active' => 'nullable|boolean',
        ];
    }
}
