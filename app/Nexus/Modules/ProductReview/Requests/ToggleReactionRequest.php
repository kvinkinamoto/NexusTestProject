<?php

namespace App\Nexus\Modules\ProductReview\Requests;

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
            'review_id' => ['nullable', Rule::exists('reviews', 'id'), 'required_without:review_comment_id'],
            'review_comment_id' => ['nullable', 'exists:review_comments,id', 'required_without:review_id'],
            'value' => ['required', 'in:1,-1'],
        ];
    }
}
