<?php

namespace App\Nexus\Modules\Order\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status?->name,
            'payment_status' => $this->payment_status?->label(),
            'products_price' => $this->products_price,
            'delivery_price' => $this->delivery_price,
            'total_price' => $this->total_price,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
