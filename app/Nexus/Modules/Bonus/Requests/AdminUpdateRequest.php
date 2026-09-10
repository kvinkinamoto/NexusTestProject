<?php

namespace App\Nexus\Modules\Bonus\Requests;

use App\Nexus\Modules\Bonus\Enums\BonusTypeEnum;
use Illuminate\Validation\Rule;
use Nodex\Nexus\Http\Requests\NexusFormRequest;

class AdminUpdateRequest extends NexusFormRequest
{
    public function rules(): array
    {
        return [
            'type' => ['sometimes', 'required', Rule::enum(BonusTypeEnum::class), Rule::unique('bonus_rules', 'type')->ignore($this->id)],
            'amount' => ['sometimes', 'required', 'numeric', 'min:0'],
            'delay_days' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'validity_days' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }
}
