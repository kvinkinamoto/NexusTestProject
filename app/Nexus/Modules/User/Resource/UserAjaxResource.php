<?php

namespace App\Nexus\Modules\User\Resource;

use Illuminate\Http\Resources\Json\JsonResource;

class UserAjaxResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'label' => "$this->email ($this->id)",
        ];
    }
}
