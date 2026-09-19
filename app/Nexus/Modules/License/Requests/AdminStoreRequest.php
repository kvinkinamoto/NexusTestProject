<?php

namespace App\Nexus\Modules\License\Requests;

use App\Nexus\Modules\License\Enums\LicenseStatus;
use Illuminate\Validation\Rule;
use Nodex\Nexus\Http\Requests\NexusFormRequest;

class AdminStoreRequest extends NexusFormRequest
{
    public function rules(): array
    {
        return [
            'license_key' => ['required', 'string', 'max:64', 'unique:licenses,license_key'],
            'status' => ['required', Rule::enum(LicenseStatus::class)],
            'max_activations' => ['required', 'integer', 'min:1'],

            'relation' => ['nullable', 'array'],
            'relation.user' => ['required', 'integer', 'exists:users,id'],
            'relation.order' => ['required', 'integer', 'exists:orders,id'],
            'relation.product' => ['required', 'integer', 'exists:shop_products,id'],
        ];
    }
}
