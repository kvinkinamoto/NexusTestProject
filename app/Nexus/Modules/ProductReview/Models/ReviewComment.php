<?php

namespace App\Nexus\Modules\ProductReview\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Kalnoy\Nestedset\NodeTrait;

class ReviewComment extends Model
{
    use NodeTrait;

    protected $table = 'review_comments';

    protected $fillable = [
        'review_id',
        'user_id',
        'guest_name',
        'guest_email',
        'parent_id',
        'comment',
        'publish',
    ];

    protected $casts = [
        'publish' => 'boolean',
    ];

    public function scopePublish($query)
    {
        return $query->where('review_comments.publish', true);
    }

    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(ReviewReaction::class);
    }

    public function getLikesCountAttribute(): int
    {
        return $this->reactions->where('value', 1)->count();
    }

    public function getDislikesCountAttribute(): int
    {
        return $this->reactions->where('value', -1)->count();
    }
}
