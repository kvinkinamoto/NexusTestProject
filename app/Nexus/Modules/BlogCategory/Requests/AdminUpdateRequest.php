<?php

namespace App\Nexus\Modules\BlogCategory\Requests;

use Illuminate\Validation\Rule;
use Nodex\Nexus\Http\Requests\NexusFormRequest;

class AdminUpdateRequest extends NexusFormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => ['nullable', 'string', 'alpha_dash', Rule::unique('blog_categories', 'slug')->ignore($this->id)],
            'description' => 'nullable|string',
        ];
    }
}
