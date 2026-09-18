<?php

namespace App\Nexus\Modules\Order\Requests;

use App\Nexus\Modules\Order\Enums\PaymentStatus;
use Illuminate\Validation\Rule;
use Nodex\Nexus\Http\Requests\NexusFormRequest;

class AdminStoreRequest extends NexusFormRequest
{
    public function rules(): array
    {
        return [
            'user_name' => ['required', 'string', 'max:255'],
            'user_last_name' => ['nullable', 'string', 'max:255'],
            'user_phone' => ['required', 'string', 'max:32'],
            'user_email' => ['nullable', 'email', 'max:255'],
            'user_comment' => ['nullable', 'string'],
            'needs_phone_call' => ['nullable', 'boolean'],

            'is_another_recipient' => ['nullable', 'boolean'],
            'recipient_name' => ['nullable', 'string', 'max:255'],
            'recipient_last_name' => ['nullable', 'string', 'max:255'],
            'recipient_phone' => ['nullable', 'string', 'max:32'],

            'delivery_method' => ['nullable', 'string', Rule::exists('deliveries', 'key')],
            'delivery_type' => ['nullable', 'string', Rule::exists('delivery_types', 'key')],
            'delivery_city' => ['nullable', 'string', 'max:255'],
            'delivery_address' => ['nullable', 'string', 'max:255'],

            'is_gift' => ['nullable', 'boolean'],
            'gift_recipient_email' => ['nullable', 'email', 'max:255'],
            'gift_message' => ['nullable', 'string', 'max:500'],
            'gift_code' => ['nullable', 'string', 'max:16'],

            'payment_method' => ['nullable', 'string', Rule::exists('payment_methods', 'code')],
            'payment_status' => ['nullable', Rule::enum(PaymentStatus::class)],

            'delivery_price' => ['nullable', 'numeric', 'min:0'],
            'admin_comment' => ['nullable', 'string'],

            'relation' => ['nullable', 'array'],
            'relation.user' => ['nullable', 'integer', 'exists:users,id'],
            'relation.recipientUser' => ['nullable', 'integer', 'exists:users,id'],
            'relation.status' => ['nullable', 'integer', 'exists:order_statuses,id'],
            'relation.promoCode' => ['nullable', 'integer', 'exists:promo_codes,id'],
        ];
    }
}
