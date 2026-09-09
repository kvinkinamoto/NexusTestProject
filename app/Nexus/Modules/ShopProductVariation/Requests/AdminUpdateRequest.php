<?php

namespace App\Nexus\Modules\ShopProductVariation\Requests;

use Nodex\Nexus\Http\Requests\NexusFormRequest;

class AdminUpdateRequest extends NexusFormRequest
{
    public function rules(): array
    {
        return [
            'relation' => ['nullable', 'array'],
            'relation.variationAttributes' => ['required', 'array', 'min:1'],
            'relation.variationAttributes.*' => ['integer', 'exists:shop_attributes,id'],
        ];
    }
}
