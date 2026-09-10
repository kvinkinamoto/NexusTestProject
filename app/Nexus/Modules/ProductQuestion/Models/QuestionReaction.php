<?php

namespace App\Nexus\Modules\ProductQuestion\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionReaction extends Model
{
    protected $table = 'question_reactions';

    protected $fillable = [
        'user_id',
        'session_id',
        'question_id',
        'value',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(ProductQuestion::class, 'question_id');
    }

    public function isLike(): bool
    {
        return $this->value === 1;
    }

    public function isDislike(): bool
    {
        return $this->value === -1;
    }
}
