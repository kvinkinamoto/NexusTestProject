<?php

namespace App\Nexus\Modules\Settings\Models;

use App\Nexus\Modules\Settings\Requests\AdminStoreRequest;
use App\Nexus\Modules\Settings\Requests\AdminUpdateRequest;
use Illuminate\Database\Eloquent\Model;
use Nodex\Nexus\Attributes\Column;
use Nodex\Nexus\Attributes\Field;
use Nodex\Nexus\Attributes\Module;
use Nodex\Nexus\Attributes\Requests;
use Nodex\Nexus\Attributes\Section;
use Nodex\Nexus\Concerns\HasAttributeSchemaProperties;

/**
 * Site-wide settings, singleton by convention: exactly one row ever exists
 * (see the booted() guards below). Not shown in the sidebar menu — the
 * admin topbar's account dropdown links straight to this row's edit page
 * (see current(), used from tailadmin/layouts/header.blade.php) rather than
 * to a list, since there is only ever one record to edit. showInMenu: false
 * follows the same precedent as Nodex\Nexus\Modules\Auth\ModuleConfiguration
 * — the module still installs, migrates and routes normally, it just isn't
 * offered as a sidebar link.
 *
 * Only the framework's default "edit" action is declared; "delete" and
 * "create" are deliberately left undeclared (they remain reachable by
 * framework default, same caveat as every other Nexus module — see
 * feedback-nexus-module-attribute-gotchas) and are neutralised instead by
 * the booted() guards, matching the pattern already used for the "Modules"
 * system row in App\Nexus\Modules\Modules\Models\Module.
 */
#[Module(
    name: 'settings',
    label: 'Site Settings',
    icon: 'solar:settings-bold',
    group: 'Site',
    showInMenu: false,
    livewire: true,
)]
#[Requests(actions: ['store' => AdminStoreRequest::class, 'update' => AdminUpdateRequest::class])]
#[Section(name: 'main', column: 'right', type: 'base', icon: 'solar:settings-bold')]
#[Section(name: 'media', column: 'right', type: 'base', icon: 'solar:gallery-bold', tab: 'media')]
class Settings extends Model
{
    use HasAttributeSchemaProperties;

    #[Column(label: 'Site name', sortable: true, searchable: true)]
    #[Field(type: 'string', section: 'main', label: 'Site name', required: true)]
    protected $site_name;

    #[Field(type: 'text', section: 'main', label: 'Site description', required: false)]
    protected $site_description;

    #[Field(type: 'boolean', section: 'main', label: 'Maintenance mode', required: false)]
    protected $maintenance_mode;

    #[Field(type: 'image', section: 'media', label: 'Logo', required: false)]
    protected $logo;

    #[Field(type: 'image', section: 'media', label: 'Favicon', required: false)]
    protected $favicon;

    protected $table = 'settings';

    protected $fillable = [
        'site_name',
        'site_description',
        'maintenance_mode',
        'logo',
        'favicon',
    ];

    protected $casts = [
        'maintenance_mode' => 'boolean',
    ];

    /**
     * The one settings row, creating it with defaults on first access so
     * every environment (fresh install, tests) always has one to edit —
     * mirrors ModuleManager auto-creating a Module row on discovery.
     */
    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'site_name' => config('app.name', 'Nexus'),
        ]);
    }

    protected static function booted(): void
    {
        static::creating(function (self $settings) {
            if (static::query()->exists()) {
                return false;
            }
        });

        static::deleting(function (self $settings) {
            return false;
        });
    }
}
