<?php

namespace App\Nexus\Modules\Shipment\Models;

use App\Nexus\Modules\Shipment\Enums\DeliveryStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderShipmentEvent extends Model
{
    protected $fillable = [
        'order_shipment_id',
        'delivery_status',
        'raw_status',
        'raw_status_code',
    ];

    protected $casts = [
        'delivery_status' => DeliveryStatus::class,
    ];

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(OrderShipment::class, 'order_shipment_id');
    }
}
