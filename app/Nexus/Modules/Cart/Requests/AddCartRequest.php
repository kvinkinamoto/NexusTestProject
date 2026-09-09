<?php

namespace App\Nexus\Modules\Cart\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => ['required', 'integer', 'min:1', 'exists:shop_products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}
