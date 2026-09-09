<?php

namespace App\Nexus\Modules\Currency\Requests;

use App\Nexus\Modules\Currency\Enums\CurrencySymbolPosition;
use Illuminate\Validation\Rule;
use Nodex\Nexus\Http\Requests\NexusFormRequest;

class AdminStoreRequest extends NexusFormRequest
{
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:10', 'unique:currencies,code'],
            'name' => ['nullable', 'array'],
            'name.*' => ['nullable', 'string', 'max:255'],
            'symbol' => ['nullable', 'string', 'max:10'],
            'symbol_position' => ['required', Rule::enum(CurrencySymbolPosition::class)],
            'rate' => ['required', 'numeric', 'min:0'],
            'is_base' => ['boolean'],
            'publish' => ['boolean'],
        ];
    }
}
