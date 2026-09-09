<?php

namespace App\Nexus\Modules\ShopAttribute\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttributeOption extends Model
{
    protected $fillable = ['attribute_id', 'value', 'color', 'order'];

    protected $casts = [
        'order' => 'integer',
    ];

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(ShopAttribute::class, 'attribute_id');
    }
}
