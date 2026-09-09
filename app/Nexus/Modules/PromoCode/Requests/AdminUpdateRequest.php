<?php

namespace App\Nexus\Modules\PromoCode\Requests;

use App\Nexus\Modules\DiscountRule\Models\DiscountRule;
use App\Nexus\Modules\PromoCode\Models\PromoCode;
use Nodex\Nexus\Http\Requests\NexusFormRequest;

class AdminUpdateRequest extends NexusFormRequest
{
    public function rules(): array
    {
        return [
            'code' => [
                'required',
                'string',
                'max:64',
                // See AdminStoreRequest::rules() for why this is a closure and
                // not Rule::unique().
                function (string $attribute, mixed $value, \Closure $fail) {
                    $normalized = mb_strtoupper(trim((string) $value));
                    $exists = PromoCode::query()
                        ->whereRaw('UPPER(code) = ?', [$normalized])
                        ->where('id', '!=', $this->id)
                        ->exists();
                    if ($exists) {
                        $fail(__('validation.unique', ['attribute' => $attribute]));
                    }
                },
            ],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after:starts_at'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'usage_limit_per_user' => ['nullable', 'integer', 'min:1'],
            'publish' => ['boolean'],

            'relation' => ['nullable', 'array'],
            'relation.discountRule' => [
                'required',
                'integer',
                'exists:discount_rules,id',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $rule = DiscountRule::find($value);
                    if ($rule && ! $rule->type->isCartLevel()) {
                        $fail(__('promoCode::translate.discount_rule_must_be_cart_level'));
                    }
                },
            ],
        ];
    }
}
