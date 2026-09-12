<?php

namespace App\Nexus\Modules\Shipment\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderShipmentStatusResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'delivery_status' => $this->delivery_status->value,
            'delivery_status_label' => $this->delivery_status->label(),
            'raw_status' => $this->raw_status,
            'last_checked_at' => $this->last_checked_at?->timezone(config('app.timezone'))->format('d.m.Y H:i'),
        ];
    }
}
