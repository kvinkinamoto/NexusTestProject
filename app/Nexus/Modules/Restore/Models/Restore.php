<?php

namespace App\Nexus\Modules\Restore\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Nodex\Nexus\Attributes\Column;
use Nodex\Nexus\Attributes\Field;
use Nodex\Nexus\Attributes\Module;
use Nodex\Nexus\Attributes\Permission;
use Nodex\Nexus\Attributes\Section;
use Nodex\Nexus\Attributes\TableAction;
use Nodex\Nexus\Attributes\TableFilter;
use Nodex\Nexus\Attributes\TableGroupAction;
use Nodex\Nexus\Concerns\HasAttributeSchemaProperties;

/**
 * Rows are populated only by RestoreService::scanBackups() reading the same
 * disk/path Backup's own #[Setting]s point at — there is no manual
 * create/edit form (the 'create' action is registered disabled, matching the
 * ActivityLog module's read-only pattern), matching the old
 * ZentaraStartProject Restore module exactly (its form() callback was empty
 * and its 'create' main action was replaced by 'scan').
 */
#[Module(
    name: 'restore',
    label: 'Restore',
    icon: 'solar:refresh-bold',
    group: 'Site',
    showInMenu: true,
    livewire: true,
    requires: ['Backup'],
)]
#[TableAction(name: 'create', label: 'Create', icon: 'create', isActive: false, isMain: true)]
#[TableAction(name: 'scan', label: 'Scan backups', icon: 'search', isConfirm: false, isMain: true)]
#[TableAction(name: 'runRestore', label: 'Run restore', icon: 'play', isConfirm: true)]
#[TableAction(name: 'delete', label: 'Delete', icon: 'delete', isConfirm: true)]
#[TableAction(name: 'restore', label: 'Restore row', icon: 'restore', isConfirm: true)]
#[TableAction(name: 'deletePermanent', label: 'Delete permanent', icon: 'delete', isConfirm: true)]
#[TableGroupAction(name: 'deleteGroup', fieldName: 'delete')]
#[TableFilter(name: 'filename', label: 'Search by filename', type: 'search')]
#[Permission(action: 'scan', label: 'Scan backups')]
#[Permission(action: 'runRestore', label: 'Run restore')]
#[Section(name: 'main', column: 'right', type: 'base', icon: 'solar:box-bold')]
class Restore extends Model
{
    use HasAttributeSchemaProperties, SoftDeletes;

    #[Column(label: 'Filename', sortable: true, searchable: true)]
    #[Field(type: 'string', section: 'main', label: 'Filename', required: false, disabled: true)]
    protected $filename;

    #[Column(label: 'Type', sortable: true)]
    #[Field(type: 'string', section: 'main', label: 'Type', required: false, disabled: true)]
    protected $type;

    #[Column(label: 'Size', sortable: false)]
    #[Field(type: 'string', section: 'main', label: 'Size', required: false, disabled: true)]
    protected $size;

    #[Column(label: 'Last modified', sortable: true)]
    #[Field(type: 'datetime', section: 'main', label: 'Last modified', required: false, disabled: true)]
    protected $last_modified;

    #[Column(label: 'Status', sortable: true)]
    #[Field(type: 'string', section: 'main', label: 'Status', required: false, disabled: true)]
    protected $status;

    #[Field(type: 'text', section: 'main', label: 'Last error', required: false, disabled: true)]
    protected $last_error;

    protected $fillable = [
        'filename',
        'disk',
        'path',
        'type',
        'size',
        'last_modified',
        'status',
        'last_error',
    ];

    protected function casts(): array
    {
        return [
            'last_modified' => 'datetime',
        ];
    }

    public function markFailed(string $message): void
    {
        $this->forceFill([
            'status' => 'failed',
            'last_error' => $message,
        ])->save();
    }
}
