<?php

namespace App\Nexus\Modules\ProductReview\Requests;

use Nodex\Nexus\Http\Requests\NexusFormRequest;

class AdminUpdateRequest extends NexusFormRequest
{
    public function rules(): array
    {
        return [
            'user_name' => ['nullable', 'string', 'max:255'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string'],
            'advantages' => ['nullable', 'string', 'max:255'],
            'disadvantages' => ['nullable', 'string', 'max:255'],
            'video' => ['nullable', 'string', 'max:255'],
            'publish' => ['boolean'],

            'relation' => ['nullable', 'array'],
            'relation.product' => ['required', 'integer', 'exists:shop_products,id'],
            'relation.user' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }
}
