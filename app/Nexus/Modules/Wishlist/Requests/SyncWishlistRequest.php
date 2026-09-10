<?php

namespace App\Nexus\Modules\Wishlist\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SyncWishlistRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items' => ['present', 'array'],
            'items.*' => ['integer', 'exists:shop_products,id'],
        ];
    }
}
