<?php

namespace App\Nexus\Modules\Plugins\Models;

use App\Nexus\Modules\Plugins\Requests\AdminStoreRequest;
use App\Nexus\Modules\Plugins\Requests\AdminUpdateRequest;
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
 * Admin-facing enable/disable for plugins under app/Nexus/Plugins/{Name}/,
 * mirroring app/Nexus/Modules/Modules (see that class's docblock for why
 * this can't just be a subclass of anything — PHP attribute reflection
 * doesn't inherit). One row per plugin *folder*, not per PHP class inside
 * it — PluginManager::autoDiscover() reflects every .php file directly
 * under a plugin folder, but a folder normally has exactly one
 * #[TargetModule]-carrying entry point (see GraphQLPlugin/BlockTypesPlugin/
 * ExamplePlugin) with the rest as supporting classes, so "the plugin" a
 * human would want to toggle is the folder, not any one file in it.
 *
 * No is_system guard here (unlike Modules): disabling any plugin can never
 * lock an admin out of this screen, since Plugin management is itself a
 * module, not a plugin.
 */
#[ModuleAttr(
    name: 'plugins',
    label: 'Керування плагінами',
    icon: 'solar:plug-circle-bold',
    group: 'Site',
    showInMenu: true,
    livewire: true,
)]
#[Requests(actions: ['store' => AdminStoreRequest::class, 'update' => AdminUpdateRequest::class])]
#[TableFilter(name: 'search', label: 'Search by Name', type: 'search')]
#[TableAction(name: 'edit', label: 'View', icon: 'edit', isConfirm: false)]
#[Section(name: 'main', column: 'right', type: 'base', icon: 'solar:box-bold')]
class Plugin extends Model
{
    use HasAttributeSchemaProperties;

    #[Column(label: 'Name', sortable: true, searchable: true)]
    #[Field(type: 'string', section: 'main', label: 'Name', required: false, disabled: true)]
    protected $name;

    #[Column(label: 'Enabled', sortable: true, action: 'boolToggle', fieldName: 'is_enabled', actionConfirm: true)]
    #[Field(type: 'boolean', section: 'main', label: 'Enabled', required: false, disabled: true)]
    protected $is_enabled;

    protected $table = 'nexus_plugins';

    protected $fillable = ['name', 'is_enabled'];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];

    /**
     * Case-insensitive lookup, same rationale as Nodex\Nexus\Models\Module::
     * findByName() — a plugin folder is discovered as its literal on-disk
     * StudlyCase name (e.g. 'GraphQL'), and nothing normalizes casing before
     * it reaches here.
     */
    public static function findByName(string $name): ?self
    {
        return static::query()->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->first();
    }
}
