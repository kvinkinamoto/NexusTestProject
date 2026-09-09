<?php

namespace App\Nexus\Modules\Cart\Http\Resources;

use App\Nexus\Modules\Currency\Services\CurrencyService;
use App\Nexus\Modules\ShopProduct\Services\Actions\ResolveProductStockAction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $product = $this->resource->product;
        $price = $product->effectivePrice();

        return [
            'id' => $product->id,
            'slug' => $product->slug,
            'title' => $product->name,
            'price' => $price,
            'price_converted' => CurrencyService::convertToSessionCurrency($price),
            'stock' => ResolveProductStockAction::handle($product),
            'image' => $product->image ? asset($product->image) : null,
            'quantity' => $this->resource->quantity,
        ];
    }
}
