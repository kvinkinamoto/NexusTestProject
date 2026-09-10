<?php

namespace App\Nexus\Modules\Order\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class QuickOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_name' => ['nullable', 'string', 'min:2', 'max:50'],
            'user_phone' => ['required', 'string', 'max:20'],
            'product_id' => ['required', Rule::exists('shop_products', 'id')->where('publish', 1)],
            'quantity' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.exists' => __('order::translate.product_not_found'),
        ];
    }
}
