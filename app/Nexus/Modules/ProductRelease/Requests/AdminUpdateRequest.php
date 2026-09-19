<?php

namespace App\Nexus\Modules\ProductRelease\Requests;

use Nodex\Nexus\Http\Requests\NexusFormRequest;

class AdminUpdateRequest extends NexusFormRequest
{
    public function rules(): array
    {
        return [
            'version' => ['required', 'string', 'max:50'],
            'github_tag' => ['sometimes', 'nullable', 'string', 'max:100'],
            'changelog' => ['nullable', 'string'],
            'min_core_version' => ['nullable', 'string', 'max:20'],
            'max_core_version' => ['nullable', 'string', 'max:20'],
            'published_at' => ['nullable', 'date'],

            'relation' => ['nullable', 'array'],
            'relation.product' => ['required', 'integer', 'exists:shop_products,id'],
        ];
    }
}
