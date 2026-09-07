<?php

namespace App\Nexus\Modules\BlogPost\Requests;

use Illuminate\Validation\Rule;
use Nodex\Nexus\Http\Requests\NexusFormRequest;

class AdminUpdateRequest extends NexusFormRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'slug' => ['nullable', 'string', 'alpha_dash', Rule::unique('blog_posts', 'slug')->ignore($this->id)],
            'is_published' => 'nullable|boolean',
            'published_at' => 'nullable|date',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'excerpt' => 'nullable|string',
            'content' => 'nullable|string',
            'featured_image' => 'nullable|string',

            'relation.category' => 'nullable|integer|exists:blog_categories,id',
            'relation.tags' => 'nullable|array',
            'relation.tags.*' => 'nullable|integer|exists:tags,id',
        ];
    }
}
