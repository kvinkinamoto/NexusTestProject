<?php

namespace App\Nexus\Modules\Page\Requests;

use Nodex\Nexus\Http\Requests\NexusFormRequest;

class AdminStoreRequest extends NexusFormRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|alpha_dash|unique:pages,slug',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'is_active' => 'nullable|boolean',

            'relation' => 'nullable|array',
            'relation.blocks' => 'nullable|array',
            'relation.blocks.*.type' => 'required|string',
            'relation.blocks.*.data' => 'nullable|array',
        ];
    }
}
