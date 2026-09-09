<?php

namespace App\Nexus\Concerns;

use Illuminate\Support\Str;

/**
 * A #[Field(type: 'slug', slugSource: ...)] column is only ever populated by
 * the admin form's manual "Generate" button (Livewire\ModuleForm::generateSlug())
 * — the Nexus package does nothing to fill it automatically on save. Every
 * migration in this codebase declares that column NOT NULL with no default,
 * so a user who types a name/title and saves without clicking Generate hits
 * a raw SQLSTATE[23000] "Column cannot be null" instead of a friendly
 * validation error (real bug, reported against the Menu module, but the
 * same latent crash exists on every Tier 2 module built this session that
 * has a slug/key field). Fixed here at the model level, scoped to the
 * modules that need it — not in the package, which would also change
 * behavior for already-shipped Tier 1 modules (Page/Form) no one asked to
 * touch.
 */
trait HasAutoSlug
{
    protected static function bootHasAutoSlug(): void
    {
        static::creating(function (self $model) {
            $column = $model->slugColumn();
            $source = $model->slugSourceColumn();

            if (empty($model->{$column}) && ! empty($model->{$source})) {
                $model->{$column} = Str::slug($model->{$source});
            }
        });
    }

    protected function slugColumn(): string
    {
        return 'slug';
    }

    protected function slugSourceColumn(): string
    {
        return 'name';
    }
}
