<?php

namespace App\Nexus\Modules\Language\Requests;

use Illuminate\Validation\Rule;
use Nodex\Nexus\Http\Requests\NexusFormRequest;

class AdminUpdateRequest extends NexusFormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_default' => $this->boolean('is_default'),
            'publish' => $this->boolean('publish'),
            'is_rtl' => $this->boolean('is_rtl'),
        ]);
    }

    public function rules(): array
    {
        return [
            'language' => ['required', 'string', 'max:100', Rule::unique('languages', 'language')->ignore($this->id)],
            'code' => ['required', 'string', 'max:10', Rule::unique('languages', 'code')->ignore($this->id)],
            'is_default' => 'boolean',
            'publish' => 'boolean',
            'is_rtl' => 'boolean',
        ];
    }
}
