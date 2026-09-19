<?php

namespace App\Nexus\Modules\ProductRelease\Requests;

use Illuminate\Validation\Rule;
use Nodex\Nexus\Http\Requests\NexusFormRequest;

class AdminStoreRequest extends NexusFormRequest
{
    public function rules(): array
    {
        return [
            'version' => ['required', 'string', 'max:50'],
            'github_tag' => [
                'required', 'string', 'max:100',
                Rule::unique('product_releases')->where('product_id', $this->input('relation.product')),
            ],
            'changelog' => ['nullable', 'string'],
            'min_core_version' => ['nullable', 'string', 'max:20'],
            'max_core_version' => ['nullable', 'string', 'max:20'],
            'published_at' => ['nullable', 'date'],

            'relation' => ['nullable', 'array'],
            'relation.product' => ['required', 'integer', 'exists:shop_products,id'],
        ];
    }
}
