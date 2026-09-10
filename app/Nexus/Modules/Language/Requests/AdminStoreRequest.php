<?php

namespace App\Nexus\Modules\Language\Requests;

use Nodex\Nexus\Http\Requests\NexusFormRequest;

class AdminStoreRequest extends NexusFormRequest
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
            'language' => 'required|string|max:100|unique:languages,language',
            'code' => 'required|string|max:10|unique:languages,code',
            'is_default' => 'boolean',
            'publish' => 'boolean',
            'is_rtl' => 'boolean',
        ];
    }
}
