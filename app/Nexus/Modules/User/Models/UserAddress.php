<?php

namespace App\Nexus\Modules\User\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAddress extends Model
{
    protected $table = 'user_addresses';

    protected $fillable = [
        'user_id',
        'city',
        'street',
        'house',
        'apartment',
        'is_main',
    ];

    protected function casts(): array
    {
        return [
            'is_main' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
