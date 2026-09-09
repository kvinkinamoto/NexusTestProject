<?php

namespace App\Nexus\Modules\ShopAttribute\Requests;

use App\Nexus\Modules\ShopAttribute\Enums\AttributeType;
use Illuminate\Validation\Rule;
use Nodex\Nexus\Http\Requests\NexusFormRequest;

class AdminStoreRequest extends NexusFormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'array'],
            'name.*' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'array'],
            'description.*' => ['nullable', 'string'],
            'type' => ['required', Rule::enum(AttributeType::class)],
            'key' => ['nullable', 'alpha_dash', 'max:255'],
            'publish' => ['boolean'],
            'merges_catalog_cards' => ['boolean'],

            'relation' => ['nullable', 'array'],
            'relation.options' => ['nullable', 'array'],
            'relation.options.*.value' => ['required', 'string', 'max:255'],
            'relation.options.*.color' => ['nullable', 'string', 'max:32'],
        ];
    }
}
