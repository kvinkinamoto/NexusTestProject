<?php

namespace App\Nexus\Modules\Wishlist\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RemoveWishlistRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:shop_products,id'],
        ];
    }
}
