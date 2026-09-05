<?php

namespace App\Nexus\Modules\Page\Requests;

use Illuminate\Validation\Rule;
use Nodex\Nexus\Http\Requests\NexusFormRequest;

class AdminUpdateRequest extends NexusFormRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'slug' => ['nullable', 'string', 'alpha_dash', Rule::unique('pages', 'slug')->ignore($this->id)],
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
