<?php

namespace App\Nexus\Modules\Form\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormField extends Model
{
    protected $fillable = ['form_id', 'key', 'label', 'type', 'required', 'options', 'order'];

    protected $casts = [
        'required' => 'boolean',
        'order' => 'integer',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    /**
     * One choice per line in the admin's plain-text 'options' column — the
     * only structured/key-value field type this package ships is a
     * read-only JSON display (see Field(type: 'json')), so a repeater child
     * row can't hold its own nested option rows. Used by both the public
     * form partial (rendering select/radio/checkbox choices) and validation
     * (building the 'in:' rule for this field at submit time).
     */
    public function optionsList(): array
    {
        return collect(preg_split('/\r\n|\r|\n/', (string) $this->options))
            ->map(fn ($line) => trim($line))
            ->filter(fn ($line) => $line !== '')
            ->values()
            ->all();
    }
}
