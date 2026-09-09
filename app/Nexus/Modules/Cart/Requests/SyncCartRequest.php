<?php

namespace App\Nexus\Modules\Cart\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SyncCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'products' => ['required', 'array'],
            'products.*.id' => ['required', 'integer', 'min:1'],
            'products.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}
