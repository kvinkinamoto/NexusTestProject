<?php

namespace App\Nexus\Modules\ProductQuestion\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductQuestionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'user_id' => $this->user_id,
            'guest_name' => $this->guest_name,
            'guest_email' => $this->guest_email,
            'parent_id' => $this->parent_id,
            'text' => $this->text,
            'publish' => $this->publish,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
