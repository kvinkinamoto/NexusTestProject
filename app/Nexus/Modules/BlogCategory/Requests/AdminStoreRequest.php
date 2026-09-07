<?php

namespace App\Nexus\Modules\BlogCategory\Requests;

use Nodex\Nexus\Http\Requests\NexusFormRequest;

class AdminStoreRequest extends NexusFormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|alpha_dash|unique:blog_categories,slug',
            'description' => 'nullable|string',
        ];
    }
}
