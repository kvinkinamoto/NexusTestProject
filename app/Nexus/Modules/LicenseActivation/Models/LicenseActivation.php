<?php

namespace App\Nexus\Modules\LicenseActivation\Models;

use App\Nexus\Modules\License\Models\License;
use App\Nexus\Modules\LicenseActivation\Requests\AdminStoreRequest;
use App\Nexus\Modules\LicenseActivation\Requests\AdminUpdateRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Nodex\Nexus\Attributes\Column;
use Nodex\Nexus\Attributes\Field;
use Nodex\Nexus\Attributes\Module;
use Nodex\Nexus\Attributes\Relation;
use Nodex\Nexus\Attributes\Requests;
use Nodex\Nexus\Attributes\Section;
use Nodex\Nexus\Attributes\TableAction;
use Nodex\Nexus\Attributes\TableGroupAction;
use Nodex\Nexus\Concerns\HasAttributeSchemaProperties;

/**
 * One row per project a buyer has activated a License against — created by
 * AccountLicensesController::activate() (identifier = the domain/URL the
 * buyer registers), deleted by ::deactivate() to free the slot back up.
 * License::max_activations caps how many of these a single License may have
 * at once; enforced in the controller, not here.
 */
#[Module(
    name: 'licenseActivation',
    label: 'Активації ліцензій',
    icon: 'solar:link-bold',
    group: 'Shop',
    showInMenu: true,
    livewire: true,
    requires: ['License'],
)]
#[TableAction(name: 'edit', label: 'Edit', icon: 'edit', isConfirm: false)]
#[TableAction(name: 'delete', label: 'Delete', icon: 'delete', isConfirm: true)]
#[TableGroupAction(name: 'deleteGroup', fieldName: 'delete')]
#[Requests(actions: ['store' => AdminStoreRequest::class, 'update' => AdminUpdateRequest::class])]
#[Section(name: 'main', column: 'right', type: 'base', icon: 'solar:link-bold')]
class LicenseActivation extends Model
{
    use HasAttributeSchemaProperties;

    #[Field(type: 'relation', section: 'main', label: 'Ліцензія', required: true)]
    #[Relation(type: 'belongsTo', show: 'license_key')]
    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }

    #[Column(label: 'Проєкт', sortable: true, searchable: true)]
    #[Field(type: 'string', section: 'main', label: 'Проєкт', required: true, rules: ['max:255'])]
    protected $identifier;

    #[Column(label: 'Активовано', sortable: true)]
    #[Field(type: 'datetime', section: 'main', label: 'Активовано', required: true)]
    protected $activated_at;

    protected $fillable = ['license_id', 'identifier', 'activated_at'];

    protected $casts = [
        'activated_at' => 'datetime',
    ];
}
