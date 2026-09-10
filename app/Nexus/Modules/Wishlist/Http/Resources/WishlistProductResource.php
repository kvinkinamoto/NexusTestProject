<?php

namespace App\Nexus\Modules\Wishlist\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WishlistProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->name,
            'image' => $this->image ? asset($this->image) : null,
            'price' => $this->effectivePrice(),
            'price_converted' => $this->priceConverted(),
        ];
    }
}
