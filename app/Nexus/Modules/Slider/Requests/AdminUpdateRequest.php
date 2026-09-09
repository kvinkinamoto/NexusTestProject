<?php

namespace App\Nexus\Modules\Slider\Requests;

use Illuminate\Validation\Rule;
use Nodex\Nexus\Http\Requests\NexusFormRequest;

class AdminUpdateRequest extends NexusFormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'key' => ['nullable', 'string', 'alpha_dash', Rule::unique('sliders', 'key')->ignore($this->id)],
            'is_active' => 'nullable|boolean',

            'relation' => 'nullable|array',
            'relation.slides' => 'nullable|array',
            'relation.slides.*.image' => 'required|string',
            'relation.slides.*.title' => 'nullable|string|max:255',
            'relation.slides.*.subtitle' => 'nullable|string|max:255',
            'relation.slides.*.link_url' => 'nullable|string|max:2048',
            'relation.slides.*.link_text' => 'nullable|string|max:255',
        ];
    }
}
