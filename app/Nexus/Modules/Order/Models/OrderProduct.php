<?php

namespace App\Nexus\Modules\Order\Models;

use App\Nexus\Modules\ShopProduct\Models\ShopProduct;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderProduct extends Model
{
    protected $table = 'order_products';

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'price',
        'total_price',
        'product_data',
    ];

    protected $casts = [
        'product_data' => 'array',
        'quantity' => 'integer',
        'price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(ShopProduct::class, 'product_id');
    }

    public function localizedField(string $field, ?string $locale = null): ?string
    {
        $value = $this->product_data[$field] ?? null;

        if (is_array($value)) {
            $locale ??= app()->getLocale();

            return $value[$locale] ?? $value['en'] ?? array_values($value)[0] ?? null;
        }

        return $value;
    }
}
