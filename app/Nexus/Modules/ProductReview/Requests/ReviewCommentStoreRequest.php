<?php

namespace App\Nexus\Modules\ProductReview\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviewCommentStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'review_id' => ['required', Rule::exists('reviews', 'id')],
            'parent_id' => ['nullable', 'exists:review_comments,id'],
            'comment' => ['required', 'string', 'max:1000'],
            'name' => ['required_without:auth', 'string', 'max:50'],
            'email' => ['required_without:auth', 'email', 'max:50'],
        ];
    }
}
