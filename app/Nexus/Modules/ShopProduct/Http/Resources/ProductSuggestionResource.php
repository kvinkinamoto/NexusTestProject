<?php

namespace App\Nexus\Modules\ShopProduct\Http\Resources;

use App\Nexus\Modules\ShopProduct\Services\Actions\ResolveProductPriceAction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductSuggestionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $pricing = ResolveProductPriceAction::handle($this->resource, 1);

        return [
            'name' => $this->displayName(),
            'slug' => $this->slug,
            'image' => $this->image ? '/'.ltrim($this->image, '/') : null,
            'category' => $this->category?->displayName(),
            'price' => $pricing['price'],
            'original_price' => $pricing['original_price'],
            'url' => route('shop.product', $this->slug),
        ];
    }
}
