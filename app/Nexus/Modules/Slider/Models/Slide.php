<?php

namespace App\Nexus\Modules\Slider\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Slide extends Model
{
    protected $fillable = ['slider_id', 'title', 'subtitle', 'image', 'link_url', 'link_text', 'order'];

    protected $casts = [
        'order' => 'integer',
    ];

    public function slider(): BelongsTo
    {
        return $this->belongsTo(Slider::class);
    }
}
