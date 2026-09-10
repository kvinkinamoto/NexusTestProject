<?php

namespace App\Nexus\Modules\ProductQuestion\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class QuestionStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', Rule::exists('shop_products', 'id')],
            'parent_id' => ['nullable', Rule::exists('product_questions', 'id')],
            'text' => ['required', 'string', 'max:1000'],
            'name' => ['required_without:auth', 'string', 'max:50'],
            'email' => ['required_without:auth', 'email', 'max:50'],
        ];
    }
}
