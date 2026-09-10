<?php

namespace App\Nexus\Modules\ProductReview\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewImage extends Model
{
    protected $table = 'review_images';

    protected $fillable = [
        'review_id',
        'url',
    ];

    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }
}
