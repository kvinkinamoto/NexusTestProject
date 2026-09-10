<?php

namespace App\Nexus\Modules\PaymentMethod\Requests;

use Illuminate\Validation\Rule;
use Nodex\Nexus\Http\Requests\NexusFormRequest;

/**
 * ZentaraStartProject's own AdminStoreRequest had empty rules() — dead
 * validation, not a deliberate design choice — so this is written fresh
 * against the actual model fields rather than ported.
 */
class AdminStoreRequest extends NexusFormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['required', 'array'],
            'title.*' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'array'],
            'description.*' => ['nullable', 'string'],
            'code' => ['required', 'string', 'max:64', Rule::unique('payment_methods', 'code')],
            'provider' => ['required', 'string', 'max:64'],
            'type' => ['required', 'string', 'max:64'],
            'count_installment' => ['nullable', 'integer', 'min:2'],
            'publish' => ['boolean'],
        ];
    }
}
