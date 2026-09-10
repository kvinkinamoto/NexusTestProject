<?php

namespace App\Nexus\Modules\ProductReview\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewReaction extends Model
{
    protected $table = 'review_reactions';

    protected $fillable = [
        'user_id',
        'session_id',
        'review_id',
        'review_comment_id',
        'value',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }

    public function comment(): BelongsTo
    {
        return $this->belongsTo(ReviewComment::class, 'review_comment_id');
    }

    public function isLike(): bool
    {
        return (int) $this->value === 1;
    }

    public function isDislike(): bool
    {
        return (int) $this->value === -1;
    }
}
