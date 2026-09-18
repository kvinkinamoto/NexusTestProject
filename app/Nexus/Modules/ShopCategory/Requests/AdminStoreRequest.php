<?php

namespace App\Nexus\Modules\ShopCategory\Requests;

use Illuminate\Validation\Rule;
use Nodex\Nexus\Http\Requests\NexusFormRequest;

class AdminStoreRequest extends NexusFormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'alpha_dash', Rule::unique('shop_categories', 'slug')],
            'short_description' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'string'],
            'nav_group' => ['nullable', 'string', 'max:255'],
            'publish' => ['boolean'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],

            'relation' => ['nullable', 'array'],
            'relation.parent' => ['nullable', 'integer', 'exists:shop_categories,id'],
        ];
    }
}
