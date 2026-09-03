<?php

namespace App\Nexus\Modules\Demo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DemoItem extends Model
{
    protected $fillable = ['demo_id', 'title', 'qty'];

    public function demo(): BelongsTo
    {
        return $this->belongsTo(Demo::class);
    }
}
