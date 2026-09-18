<?php

namespace App\Nexus\Modules\Order\Requests;

use App\Nexus\Modules\Order\Enums\PaymentStatus;
use Illuminate\Validation\Rule;
use Nodex\Nexus\Http\Requests\NexusFormRequest;

class AdminUpdateRequest extends NexusFormRequest
{
    public function rules(): array
    {
        return [
            'user_name' => ['sometimes', 'required', 'string', 'max:255'],
            'user_last_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'user_phone' => ['sometimes', 'required', 'string', 'max:32'],
            'user_email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'user_comment' => ['sometimes', 'nullable', 'string'],
            'needs_phone_call' => ['sometimes', 'nullable', 'boolean'],

            'is_another_recipient' => ['sometimes', 'nullable', 'boolean'],
            'recipient_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'recipient_last_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'recipient_phone' => ['sometimes', 'nullable', 'string', 'max:32'],

            'delivery_method' => ['sometimes', 'nullable', 'string', Rule::exists('deliveries', 'key')],
            'delivery_type' => ['sometimes', 'nullable', 'string', Rule::exists('delivery_types', 'key')],
            'delivery_city' => ['sometimes', 'nullable', 'string', 'max:255'],
            'delivery_address' => ['sometimes', 'nullable', 'string', 'max:255'],

            'is_gift' => ['sometimes', 'nullable', 'boolean'],
            'gift_recipient_email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'gift_message' => ['sometimes', 'nullable', 'string', 'max:500'],
            'gift_code' => ['sometimes', 'nullable', 'string', 'max:16'],

            'payment_method' => ['sometimes', 'nullable', 'string', Rule::exists('payment_methods', 'code')],
            'payment_status' => ['sometimes', 'nullable', Rule::enum(PaymentStatus::class)],

            'delivery_price' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'admin_comment' => ['sometimes', 'nullable', 'string'],

            'relation' => ['sometimes', 'array'],
            'relation.user' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
            'relation.recipientUser' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
            'relation.status' => ['sometimes', 'nullable', 'integer', 'exists:order_statuses,id'],
            'relation.promoCode' => ['sometimes', 'nullable', 'integer', 'exists:promo_codes,id'],
        ];
    }
}
