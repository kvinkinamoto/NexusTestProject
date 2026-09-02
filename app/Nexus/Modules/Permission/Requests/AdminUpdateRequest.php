<?php

namespace App\Nexus\Modules\Permission\Requests;

use Nodex\Nexus\Http\Requests\NexusFormRequest;

class AdminUpdateRequest extends NexusFormRequest
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
            'relation.roles' => 'nullable|array',
            'relation.roles.*' => 'nullable|int',
            'relation.users' => 'nullable|array',
            'relation.users.*' => 'nullable|int',

            'display_name' => ['nullable', 'array'],
            'display_name.*' => ['nullable', 'string'],
        ];
    }
}
