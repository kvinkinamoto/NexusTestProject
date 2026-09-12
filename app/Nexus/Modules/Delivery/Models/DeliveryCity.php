<?php

namespace App\Nexus\Modules\Delivery\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A city/settlement cached from a carrier's own address classifier (Nova
 * Poshta's Address.getCities, Ukrposhta's eCom directory), keyed by the
 * carrier's own `ref` so a re-sync can upsert instead of duplicating. Not a
 * Nexus admin module on purpose — this holds tens of thousands of rows per
 * carrier, useless to browse/edit through the generic CRUD table; it exists
 * only to back the checkout's city/warehouse picker (see
 * DeliveryAddressSyncService) and Order.delivery_city/_address stay plain
 * strings filled in from a row here, no schema change needed there.
 */
class DeliveryCity extends Model
{
    protected $fillable = ['delivery_id', 'ref', 'name', 'region'];

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class);
    }

    public function warehouses(): HasMany
    {
        return $this->hasMany(DeliveryWarehouse::class);
    }
}
