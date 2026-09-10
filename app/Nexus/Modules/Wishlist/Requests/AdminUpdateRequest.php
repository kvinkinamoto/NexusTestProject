<?php

namespace App\Nexus\Modules\Wishlist\Requests;

use App\Nexus\Modules\Wishlist\Models\WishlistItem;
use Nodex\Nexus\Http\Requests\NexusFormRequest;

class AdminUpdateRequest extends NexusFormRequest
{
    public function rules(): array
    {
        return [
            'relation' => ['required', 'array'],
            'relation.user' => ['required', 'integer', 'exists:users,id'],
            'relation.product' => ['required', 'integer', 'exists:shop_products,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $exists = WishlistItem::query()
                ->where('user_id', data_get($this->input('relation'), 'user'))
                ->where('product_id', data_get($this->input('relation'), 'product'))
                ->whereKeyNot($this->route('wishlistItem'))
                ->exists();

            if ($exists) {
                $validator->errors()->add('relation.product', __('wishlist::translate.already_exists'));
            }
        });
    }
}
