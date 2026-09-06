<?php

namespace App\Nexus\Modules\WidgetPlacement\Models;

use App\Nexus\Modules\WidgetPlacement\Requests\AdminStoreRequest;
use App\Nexus\Modules\WidgetPlacement\Requests\AdminUpdateRequest;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Nodex\Nexus\Attributes\Column;
use Nodex\Nexus\Attributes\Field;
use Nodex\Nexus\Attributes\Module;
use Nodex\Nexus\Attributes\Relation;
use Nodex\Nexus\Attributes\RepeaterField;
use Nodex\Nexus\Attributes\Requests;
use Nodex\Nexus\Attributes\Section;
use Nodex\Nexus\Attributes\TableAction;
use Nodex\Nexus\Attributes\TableGroupAction;
use Nodex\Nexus\Concerns\HasAttributeSchemaProperties;
use Nodex\Nexus\Models\WidgetAssignment;
use Nodex\Nexus\Models\WidgetInstance;

/**
 * The admin-manageable half of the front-placement system —
 * Nodex\Nexus\Models\WidgetInstance's own docblock always intended a
 * management screen ("#[Field]/#[Column] attributes for the admin
 * management screen land in ... a later step, not here") that never
 * actually got built. Extends WidgetInstance itself (same table, same
 * render()/resolveWidgetClass() logic — same pattern app/Nexus/Modules/Role
 * already uses to add #[Field]s on top of Spatie's own Role model) rather
 * than duplicating it, so this module is the write path FrontWidgetRenderer
 * and the Blade "position" directive were built for but never had.
 *
 * A "placement" = one configured widget instance (name, which #[Widget]
 * class, its config) + one or more positions it's shown at (the
 * `assignments` repeater below, backed by the package's own
 * widget_assignments table).
 */
#[Module(
    name: 'widgetPlacement',
    label: 'Widget Placements',
    icon: 'solar:widget-4-bold',
    group: 'Site',
    showInMenu: true,
    livewire: true,
)]
#[TableAction(name: 'edit', label: 'Edit', icon: 'edit', isConfirm: false)]
#[TableAction(name: 'delete', label: 'Delete', icon: 'delete', isConfirm: true)]
#[TableGroupAction(name: 'deleteGroup', fieldName: 'delete')]
#[Requests(actions: ['store' => AdminStoreRequest::class, 'update' => AdminUpdateRequest::class])]
#[Section(name: 'main', column: 'right', type: 'base', icon: 'solar:widget-4-bold')]
#[Section(name: 'placements', column: 'left', type: 'base', icon: 'solar:map-point-bold')]
class WidgetPlacement extends WidgetInstance
{
    use HasAttributeSchemaProperties;

    #[Column(label: 'Name', sortable: true, searchable: true)]
    #[Field(type: 'string', section: 'main', label: 'Name', required: true)]
    protected $name;

    /**
     * Options populated by Listeners/PopulateWidgetKeyOptions.php from
     * WidgetRegistry::all(), filtered to RendersHtml implementors only —
     * a ProvidesMetric-only widget has nothing WidgetInstance::render() can
     * show here (it returns '' for anything not RendersHtml).
     */
    #[Column(label: 'Widget')]
    #[Field(type: 'select', section: 'main', label: 'Widget', required: true)]
    protected $widget_key;

    #[Field(type: 'boolean', section: 'main', label: 'Enabled', required: false, default: true)]
    #[Column(label: 'Enabled', sortable: true, action: 'boolToggle', fieldName: 'is_enabled', actionConfirm: true)]
    protected $is_enabled;

    #[Field(type: 'json', section: 'main', label: 'Config (JSON, optional)', required: false, showInInfolist: false)]
    protected $config;

    #[Field(type: 'repeater', section: 'placements', label: 'Shown at', required: false)]
    #[Relation(type: 'hasMany')]
    #[RepeaterField(name: 'position', type: 'string', label: 'Position', required: true)]
    #[RepeaterField(name: 'target_template', type: 'string', label: 'Template (blank = everywhere)', required: false)]
    #[RepeaterField(name: 'is_active', type: 'boolean', label: 'Active', required: false, width: '90px')]
    #[RepeaterField(name: 'sort_order', type: 'number', label: 'Order', required: false, width: '90px')]
    public function assignments(): HasMany
    {
        return $this->hasMany(WidgetAssignment::class, 'widget_instance_id');
    }
}
