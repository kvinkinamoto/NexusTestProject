<?php

namespace App\Nexus\Modules\ActivityLog\Models;

use Nodex\Nexus\Attributes\Column;
use Nodex\Nexus\Attributes\Field;
use Nodex\Nexus\Attributes\Module;
use Nodex\Nexus\Attributes\Section;
use Nodex\Nexus\Attributes\TableAction;
use Nodex\Nexus\Concerns\HasAttributeSchemaProperties;
use Spatie\Activitylog\Models\Activity;

/**
 * A read-only admin viewer over spatie/laravel-activitylog's own `Activity`
 * model. Ported from ZentaraStartProject's ActivityLog module, but adapted
 * to the currently-installed v5.1 (the old module targeted v4.12) — several
 * real breaking changes along the way: `batch_uuid` was dropped from the
 * package entirely (not just unused — the column no longer exists), a new
 * `attribute_changes` column replaced `properties.attributes`/`.old` as
 * where tracked model changes live, `LogsActivity` moved to
 * `Spatie\Activitylog\Models\Concerns`, `LogOptions` to
 * `Spatie\Activitylog\Support`, and the `tapActivity()` hook was renamed to
 * `beforeActivityLogged()` — see `Traits/LogsActivityWithTranslatable.php`.
 *
 * PHP attribute reflection doesn't inherit across classes, so this
 * subclass — not the package's own `Activity` — is what actually carries
 * every `#[Module]`/`#[Field]` attribute; `config('activitylog.activity_model')`
 * points here so the package logs into (and this admin screen reads) the
 * same table via the same class.
 *
 * `create` is explicitly disabled — nothing should hand-create a log row —
 * and every field is `disabled: true`: this is a viewer, not a form. The
 * `causer` field type (already built into the Nexus package, seemingly
 * anticipating this exact module — see `Promotion`/`ShopProduct`'s own
 * "ActivityLog integration (Phase 11)" docblock notes) resolves
 * `causer_type`/`causer_id` into a clickable link to the acting admin user.
 * Dropped the old module's badge-colored `event` column and the
 * `custom_index_fields/causer.blade.php` link-in-table-cell — cosmetic only,
 * no built-in Nexus mechanism for a custom list-column renderer exists
 * beyond `showImage`, and the `causer` field type already covers the
 * single-record view, which is where "who did this" actually matters.
 */
#[Module(
    name: 'activityLog',
    label: 'Activity Log',
    icon: 'solar:history-bold',
    group: 'Site',
    showInMenu: true,
    livewire: true,
)]
#[TableAction(name: 'edit', label: 'View', icon: 'edit', isConfirm: false)]
#[TableAction(name: 'create', label: 'Create', icon: 'create', isActive: false)]
#[Section(name: 'information', column: 'right', type: 'base', icon: 'solar:info-circle-bold')]
#[Section(name: 'data', column: 'left', type: 'base', icon: 'solar:database-bold')]
class ActivityLog extends Activity
{
    use HasAttributeSchemaProperties;

    #[Column(label: 'Event', sortable: true)]
    #[Field(type: 'string', section: 'information', label: 'Event', required: false, disabled: true)]
    protected $event;

    #[Column(label: 'Subject type', sortable: true)]
    #[Field(type: 'string', section: 'information', label: 'Subject type', required: false, disabled: true)]
    protected $subject_type;

    #[Field(type: 'string', section: 'information', label: 'Subject id', required: false, disabled: true)]
    protected $subject_id;

    #[Field(type: 'string', section: 'information', label: 'Causer type', required: false, disabled: true)]
    protected $causer_type;

    #[Column(label: 'Causer', sortable: true)]
    #[Field(type: 'causer', section: 'information', label: 'Causer id', required: false, disabled: true)]
    protected $causer_id;

    #[Field(type: 'string', section: 'information', label: 'Log name', required: false, disabled: true)]
    protected $log_name;

    #[Field(type: 'string', section: 'information', label: 'Description', required: false, disabled: true)]
    protected $description;

    #[Field(type: 'json', section: 'data', label: 'Attribute changes', required: false, disabled: true)]
    protected $attribute_changes;

    #[Field(type: 'json', section: 'data', label: 'Properties', required: false, disabled: true)]
    protected $properties;
}
