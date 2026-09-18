<?php

namespace App\Nexus\Modules\Advertising\Requests;

use App\Nexus\Modules\Advertising\Enums\AdvertisingType;
use Illuminate\Validation\Rule;
use Nodex\Nexus\Http\Requests\NexusFormRequest;

class AdminUpdateRequest extends NexusFormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slot' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::enum(AdvertisingType::class)],
            'content_html' => ['nullable', 'string', 'required_if:type,html'],
            'image' => ['nullable', 'string', 'required_if:type,banner'],
            'target_url' => ['nullable', 'url', 'max:2048', 'required_if:type,banner'],
            'is_active' => ['boolean'],
            'max_impressions' => ['nullable', 'integer', 'min:1'],
            'max_impressions_per_visitor' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ];
    }
}
