<?php

namespace App\Nexus\Modules\Order\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * The public checkout endpoint's request. delivery_method/delivery_type/
 * payment_method are all validated against their real tables now that
 * Delivery/DeliveryType (Phase 7) and PaymentMethod (Phase 6) exist.
 */
class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_name' => ['required', 'string', 'min:2', 'max:50'],
            'user_last_name' => ['nullable', 'string', 'min:2', 'max:50'],
            'user_phone' => ['required', 'string', 'max:30'],
            'user_email' => ['nullable', 'email', 'max:100'],
            'user_comment' => ['nullable', 'string', 'max:500'],
            'needs_phone_call' => ['nullable', 'boolean'],

            'is_another_recipient' => ['nullable', 'boolean'],
            'recipient_name' => ['required_if:is_another_recipient,true', 'nullable', 'string', 'min:2', 'max:50'],
            'recipient_last_name' => ['required_if:is_another_recipient,true', 'nullable', 'string', 'min:2', 'max:50'],
            'recipient_phone' => ['required_if:is_another_recipient,true', 'nullable', 'string', 'max:30'],

            'delivery_method' => ['nullable', 'string', Rule::exists('deliveries', 'key')->where('publish', 1)],
            'delivery_type' => ['nullable', 'string', Rule::exists('delivery_types', 'key')->where('publish', 1)],
            'delivery_city' => ['nullable', 'string', 'max:100'],
            'delivery_address' => ['nullable', 'string', 'max:255'],

            'payment_method' => ['nullable', 'string', Rule::exists('payment_methods', 'code')->where('publish', 1)],

            'products' => ['nullable', 'array'],
            'products.*.id' => ['required_with:products', 'integer'],
            'products.*.quantity' => ['required_with:products', 'integer', 'min:1'],

            'promo_code' => ['nullable', 'string', 'max:50'],
        ];
    }
}
