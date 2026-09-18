<?php

namespace App\Nexus\Modules\Order\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RedeemGiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:16'],
        ];
    }
}
