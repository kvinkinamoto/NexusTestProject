<?php

namespace App\Nexus\Modules\ShopProduct\Requests;

use Illuminate\Validation\Rule;
use Nodex\Nexus\Http\Requests\NexusFormRequest;

class AdminStoreRequest extends NexusFormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'alpha_dash', Rule::unique('shop_products', 'slug')],
            'short_description' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'price_discount' => ['nullable', 'numeric', 'min:0', 'lt:price'],
            'bonuses' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'publish' => ['boolean'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],

            'relation' => ['nullable', 'array'],
            'relation.category' => ['nullable', 'integer', 'exists:shop_categories,id'],
            'relation.type' => ['nullable', 'integer', 'exists:shop_product_types,id'],
            'relation.variationGroup' => ['nullable', 'integer', 'exists:variation_groups,id'],
            'relation.attributeOptions' => ['nullable', 'array'],
            'relation.attributeOptions.*' => ['integer', 'exists:attribute_options,id'],
            'relation.relatedProducts' => ['nullable', 'array'],
            'relation.relatedProducts.*' => ['integer', 'exists:shop_products,id'],
        ];
    }
}
