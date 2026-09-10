<?php

namespace App\Nexus\Modules\ActivityLog\Traits;

use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * Ported from ZentaraStartProject, adapted to spatie/laravel-activitylog
 * v5.1 (the old trait targeted v4.12): `Spatie\Activitylog\Traits\LogsActivity`
 * moved to `Spatie\Activitylog\Models\Concerns\LogsActivity`, `LogOptions`
 * moved to `Spatie\Activitylog\Support\LogOptions`, `dontSubmitEmptyLogs()`
 * was renamed `dontLogEmptyChanges()`, and — the real behavior change —
 * `tapActivity($activity, $event)` was renamed `beforeActivityLogged()` AND
 * v5 moved tracked attribute changes off `properties` (now user-data-only,
 * via `withProperties()`) onto a new `attribute_changes` column. The
 * decode-translatable-JSON logic now operates on `attribute_changes`
 * instead of `properties`.
 */
trait LogsActivityWithTranslatable
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return $this->getDefaultLogOptions();
    }

    protected function getDefaultLogOptions(): LogOptions
    {
        $options = LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontLogEmptyChanges();

        if (property_exists($this, 'translatable') && is_array($this->translatable)) {
            $options->useAttributeRawValues($this->translatable);
        }

        return $options;
    }

    public function beforeActivityLogged($activity, string $eventName): void
    {
        if (! property_exists($this, 'translatable') || ! is_array($this->translatable)) {
            return;
        }

        $changes = $activity->attribute_changes?->toArray() ?? [];

        foreach ($this->translatable as $attribute) {
            foreach (['attributes', 'old'] as $bucket) {
                $value = $changes[$bucket][$attribute] ?? null;

                if (is_string($value) && is_array($decoded = json_decode($value, true))) {
                    $changes[$bucket][$attribute] = $decoded;
                }
            }
        }

        $activity->attribute_changes = collect($changes);
    }
}
