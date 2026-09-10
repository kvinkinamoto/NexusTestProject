<?php

namespace App\Nexus\Modules\ProductQuestion\Requests;

use Illuminate\Validation\Rule;
use Nodex\Nexus\Http\Requests\NexusFormRequest;

class AdminUpdateRequest extends NexusFormRequest
{
    public function rules(): array
    {
        return [
            'guest_name' => ['nullable', 'string', 'max:255'],
            'guest_email' => ['nullable', 'email', 'max:255'],
            'text' => ['required', 'string', 'max:1000'],
            'publish' => ['boolean'],

            'relation' => ['nullable', 'array'],
            'relation.product' => ['required', 'integer', 'exists:shop_products,id'],
            'relation.user' => ['nullable', 'integer', 'exists:users,id'],
            'relation.parent' => [
                'nullable',
                'integer',
                Rule::exists('product_questions', 'id')->whereNot('id', $this->route('productQuestion')),
            ],
        ];
    }
}
