<?php

namespace App\Nexus\Modules\Modules\Models;

use App\Nexus\Modules\Modules\Requests\AdminStoreRequest;
use App\Nexus\Modules\Modules\Requests\AdminUpdateRequest;
use Illuminate\Database\Eloquent\Model;
use Nodex\Nexus\Attributes\Column;
use Nodex\Nexus\Attributes\Field;
use Nodex\Nexus\Attributes\Module as ModuleAttr;
use Nodex\Nexus\Attributes\Requests;
use Nodex\Nexus\Attributes\Section;
use Nodex\Nexus\Attributes\TableAction;
use Nodex\Nexus\Attributes\TableFilter;
use Nodex\Nexus\Concerns\HasAttributeSchemaProperties;

/**
 * Admin-facing view onto the same `nexus_modules` table that
 * Nodex\Nexus\Models\Module (the framework's internal is_enabled gate, used
 * by ModuleRegistry/ModuleManager and every {module} route binding) already
 * maps to — deliberately a second, independent class rather than extending
 * that one, because PHP attribute reflection only sees attributes declared
 * directly on the reflected class, never inherited ones, and the whole
 * admin CRUD system (AttributeSchemaReader) works by reflecting exactly this
 * class. This is what makes 'modules' show up as a real module at all: it
 * has to live under app/Nexus/Modules/{Name}/ for ModuleRegistry's directory
 * scan to find it (see ModuleRegistry::scanDirectory()).
 *
 * No #[TableAction] for create/delete is declared below — this screen isn't
 * where you'd install or remove a module (that's nexus:module:install,
 * which touches migrations/permissions/etc.; a raw delete() here also skips
 * ModuleManager::uninstall()'s permission cleanup). AttributeSchemaReader
 * still defaults every module to a full create/edit/delete/restore action
 * set regardless of what's declared, so both remain reachable — the create
 * form only accepts `name`, and is_system (see booted() below) blocks
 * deleting the one row that would actually break something.
 */
#[ModuleAttr(
    name: 'modules',
    label: 'Керування модулями',
    icon: 'solar:widget-5-bold',
    group: 'Site',
    showInMenu: true,
    livewire: true,
)]
#[Requests(actions: ['store' => AdminStoreRequest::class, 'update' => AdminUpdateRequest::class])]
#[TableFilter(name: 'search', label: 'Search by Name', type: 'search')]
#[TableAction(name: 'edit', label: 'View', icon: 'edit', isConfirm: false)]
#[Section(name: 'main', column: 'right', type: 'base', icon: 'solar:box-bold')]
class Module extends Model
{
    use HasAttributeSchemaProperties;

    #[Column(label: 'Name', sortable: true, searchable: true)]
    #[Field(type: 'string', section: 'main', label: 'Name', required: false, disabled: true)]
    protected $name;

    #[Column(label: 'Enabled', sortable: true, action: 'boolToggle', fieldName: 'is_enabled', actionConfirm: true)]
    #[Field(type: 'boolean', section: 'main', label: 'Enabled', required: false, disabled: true)]
    protected $is_enabled;

    #[Column(label: 'System', sortable: true)]
    #[Field(type: 'boolean', section: 'main', label: 'System', required: false, disabled: true)]
    protected $is_system;

    protected $table = 'nexus_modules';

    protected $fillable = ['name', 'is_enabled', 'is_system'];

    protected $casts = [
        'is_enabled' => 'boolean',
        'is_system' => 'boolean',
    ];

    /**
     * Same guard as Nodex\Nexus\Models\Module::booted() — needed again here
     * because it's a separate class with its own model-event dispatcher.
     * This is the class BoolToggleActionMethod/DeleteActionMethod actually
     * operate on for this admin screen ($moduleConfig->model resolves here,
     * not to Nodex\Nexus\Models\Module), so without this, disabling or
     * deleting the system module row that manages module enable/disable
     * would only be blocked on the *other* class nothing on this screen
     * calls. Verified via tinker that this is not merely defensive: without
     * it, ->save() on this class silently succeeded in turning is_enabled
     * off for an is_system row.
     */
    protected static function booted(): void
    {
        static::updating(function (self $module) {
            if ($module->is_system && $module->isDirty('is_enabled') && ! $module->is_enabled) {
                return false;
            }
        });

        static::deleting(function (self $module) {
            if ($module->is_system) {
                return false;
            }
        });
    }
}
