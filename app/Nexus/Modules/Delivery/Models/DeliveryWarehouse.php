<?php

namespace App\Nexus\Modules\Delivery\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A branch/postomat cached from a carrier's address classifier — see
 * DeliveryCity's docblock for why this isn't a Nexus admin module.
 */
class DeliveryWarehouse extends Model
{
    protected $fillable = ['delivery_id', 'delivery_city_id', 'ref', 'number', 'name', 'address', 'type'];

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(DeliveryCity::class, 'delivery_city_id');
    }
}
