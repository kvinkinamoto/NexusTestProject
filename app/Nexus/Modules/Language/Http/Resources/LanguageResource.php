<?php

namespace App\Nexus\Modules\Language\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LanguageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'language' => $this->language,
            'code' => $this->code,
            'is_default' => $this->is_default,
            'is_rtl' => $this->is_rtl,
        ];
    }
}
