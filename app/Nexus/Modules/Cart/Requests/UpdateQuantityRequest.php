<?php

namespace App\Nexus\Modules\Cart\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuantityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => ['required', 'integer', 'min:1'],
            'quantity' => ['required', 'integer', 'min:0'],
        ];
    }
}
