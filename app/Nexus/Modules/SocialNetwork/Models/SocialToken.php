<?php

namespace App\Nexus\Modules\SocialNetwork\Models;

use Illuminate\Database\Eloquent\Model;

class SocialToken extends Model
{
    protected $fillable = [
        'provider',
        'access_token',
        'refresh_token',
        'expires_at',
        'meta',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'meta' => 'array',
    ];

    public function isExpired(int $bufferSeconds = 300): bool
    {
        if (! $this->expires_at) {
            return false;
        }

        return $this->expires_at->subSeconds($bufferSeconds)->isPast();
    }
}
