<?php

namespace App\Nexus\Modules\ProductQuestion\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ToggleReactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'question_id' => ['required', Rule::exists('product_questions', 'id')],
            'value' => ['required', 'in:1,-1'],
        ];
    }
}
