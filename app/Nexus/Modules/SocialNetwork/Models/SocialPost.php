<?php

namespace App\Nexus\Modules\SocialNetwork\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialPost extends Model
{
    protected $table = 'social_posts';

    protected $fillable = [
        'social_account_id',
        'platform',
        'external_id',
        'text',
        'media',
        'post_url',
        'metrics',
        'is_visible',
        'published_at',
    ];

    protected $casts = [
        'media' => 'array',
        'metrics' => 'array',
        'published_at' => 'datetime',
        'is_visible' => 'boolean',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(SocialNetwork::class, 'social_account_id');
    }

    public function getThumbnailAttribute(): ?string
    {
        return $this->media[0]['thumbnail'] ?? $this->media[0]['url'] ?? null;
    }

    public function hasVideo(): bool
    {
        return collect($this->media)->contains('type', 'video');
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_visible', true);
    }

    public function scopeLatestFirst(Builder $query): Builder
    {
        return $query->orderByDesc('published_at');
    }

    public function scopePlatform(Builder $query, string $platform): Builder
    {
        return $query->where('platform', $platform);
    }
}
