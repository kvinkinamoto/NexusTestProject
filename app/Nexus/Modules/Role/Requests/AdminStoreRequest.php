<?php

namespace App\Nexus\Modules\Role\Requests;

use Nodex\Nexus\Http\Requests\NexusFormRequest;

class AdminStoreRequest extends NexusFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'relation' => 'nullable|array',
            'relation.permissions' => 'nullable|array',
            'relation.permissions.roles.*' => 'nullable|int',
            'relation.users' => 'nullable|array',
            'relation.users.*' => 'nullable|int',

            'display_name' => ['nullable', 'array'],
            'display_name.*' => ['nullable', 'string'],
        ];
    }
}
