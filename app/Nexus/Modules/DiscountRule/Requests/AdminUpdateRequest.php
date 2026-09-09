<?php

namespace App\Nexus\Modules\DiscountRule\Requests;

use App\Nexus\Modules\DiscountRule\Enums\DiscountRuleType;
use App\Nexus\Modules\DiscountRule\Enums\ScopeTarget;
use App\Nexus\Modules\DiscountRule\Enums\ValueType;
use Illuminate\Validation\Rule;
use Nodex\Nexus\Http\Requests\NexusFormRequest;

class AdminUpdateRequest extends NexusFormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::enum(DiscountRuleType::class)],
            'value_type' => ['nullable', Rule::enum(ValueType::class)],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'priority' => ['nullable', 'integer'],
            'stackable' => ['boolean'],
            'scope_target' => ['required', Rule::enum(ScopeTarget::class)],
            'min_cart_amount' => ['nullable', 'numeric', 'min:0'],
            'min_qty' => ['nullable', 'integer', 'min:1'],
            'day_of_week' => ['nullable', 'array'],
            'day_of_week.*' => ['integer', 'between:0,6'],
            'time_from' => ['nullable', 'date_format:H:i'],
            'time_to' => ['nullable', 'date_format:H:i'],
            'mechanics' => ['nullable', 'array'],

            'relation' => ['nullable', 'array'],
            'relation.category' => ['nullable', 'integer', 'exists:shop_categories,id'],
            'relation.products' => ['nullable', 'array'],
            'relation.products.*' => ['integer', 'exists:shop_products,id'],
            'relation.excludedProducts' => ['nullable', 'array'],
            'relation.excludedProducts.*' => ['integer', 'exists:shop_products,id'],
        ];
    }
}
