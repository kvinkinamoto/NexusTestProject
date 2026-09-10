<?php

namespace App\Nexus\Modules\Shipment\Models;

use App\Nexus\Modules\Order\Models\Order;
use App\Nexus\Modules\Shipment\Enums\DeliveryStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Nodex\Nexus\Attributes\Module;

/**
 * No admin-editable Fields — like OrderProduct/OrderHistory, this is
 * checkout/tracking-pipeline-owned data, not hand-edited in the admin.
 * Registered as a minimal, menu-hidden #[Module] purely so its routes/lang
 * auto-load, matching the Cart module's own precedent.
 */
#[Module(name: 'orderShipment', label: 'Shipments', showInMenu: false)]
class OrderShipment extends Model
{
    protected $fillable = [
        'order_id',
        'carrier',
        'tracking_number',
        'delivery_status',
        'raw_status',
        'raw_status_code',
        'last_checked_at',
        'shipped_at',
        'delivered_at',
    ];

    protected $casts = [
        'delivery_status' => DeliveryStatus::class,
        'last_checked_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Append-only status timeline — unlike this model's own delivery_status
     * (overwritten on every refresh), each row here is a preserved transition.
     */
    public function events(): HasMany
    {
        return $this->hasMany(OrderShipmentEvent::class)->orderBy('id');
    }
}
