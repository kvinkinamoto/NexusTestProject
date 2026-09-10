<?php

namespace App\Nexus\Modules\Bonus\Requests;

use App\Nexus\Modules\Bonus\Enums\BonusTypeEnum;
use Illuminate\Validation\Rule;
use Nodex\Nexus\Http\Requests\NexusFormRequest;

class AdminStoreRequest extends NexusFormRequest
{
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(BonusTypeEnum::class), Rule::unique('bonus_rules', 'type')],
            'amount' => ['required', 'numeric', 'min:0'],
            'delay_days' => ['nullable', 'integer', 'min:0'],
            'validity_days' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }
}
