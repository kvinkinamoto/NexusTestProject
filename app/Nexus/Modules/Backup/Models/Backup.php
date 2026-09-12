<?php

namespace App\Nexus\Modules\Backup\Models;

use App\Nexus\Modules\Backup\Requests\AdminStoreRequest;
use App\Nexus\Modules\Backup\Requests\AdminUpdateRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Nodex\Nexus\Attributes\Column;
use Nodex\Nexus\Attributes\Field;
use Nodex\Nexus\Attributes\Module;
use Nodex\Nexus\Attributes\Permission;
use Nodex\Nexus\Attributes\Requests;
use Nodex\Nexus\Attributes\Section;
use Nodex\Nexus\Attributes\Setting;
use Nodex\Nexus\Attributes\TableAction;
use Nodex\Nexus\Attributes\TableGroupAction;
use Nodex\Nexus\Concerns\HasAttributeSchemaProperties;

/**
 * A row is a named backup configuration (e.g. "Nightly DB only", "Weekly
 * full") rather than a single global settings singleton like the old
 * ZentaraStartProject module assumed (its BackupService always read
 * Backup::first()) — the table already carried per-row 'edit'+'run' actions,
 * which only makes sense if more than one config is expected to exist side
 * by side. RunBackupJob/BackupService take the specific row that was
 * clicked, not "whatever the first row happens to be".
 *
 * storage_disk/storage_path are module-level #[Setting]s (not table columns)
 * — Restore intentionally reads these same settings back via
 * SettingsProviderInterface::get('backup', ...) instead of declaring its own
 * copy, to avoid the "kept in sync manually" duplication the old project had
 * between its two modules' identical settings.
 */
#[Module(
    name: 'backup',
    label: 'Backup',
    icon: 'solar:database-bold',
    group: 'Site',
    showInMenu: true,
    livewire: true,
)]
#[TableAction(name: 'edit', label: 'Edit', icon: 'edit', isConfirm: false)]
#[TableAction(name: 'delete', label: 'Delete', icon: 'delete', isConfirm: true)]
#[TableAction(name: 'restore', label: 'Restore', icon: 'restore', isConfirm: true)]
#[TableAction(name: 'deletePermanent', label: 'Delete permanent', icon: 'delete', isConfirm: true)]
#[TableAction(name: 'run', label: 'Run now', icon: 'play', isConfirm: true)]
#[TableGroupAction(name: 'deleteGroup', fieldName: 'delete')]
#[Permission(action: 'run', label: 'Run backup now')]
#[Requests(actions: ['store' => AdminStoreRequest::class, 'update' => AdminUpdateRequest::class])]
#[Section(name: 'main', column: 'right', type: 'base', icon: 'solar:box-bold')]
#[Section(name: 'status', column: 'left', type: 'base', icon: 'solar:info-circle-bold')]
#[Setting(name: 'storage_disk', type: 'select', label: 'storage_disk', default: 'local', options: ['local' => 'local', 'public' => 'public', 's3' => 's3'])]
#[Setting(name: 'storage_path', type: 'string', label: 'storage_path', default: 'backups')]
class Backup extends Model
{
    use HasAttributeSchemaProperties, SoftDeletes;

    #[Column(label: 'Name', sortable: true, searchable: true)]
    #[Field(type: 'string', section: 'main', label: 'Name', required: true)]
    protected $name;

    #[Column(label: 'Database', sortable: false)]
    #[Field(type: 'boolean', section: 'main', label: 'Backup database', required: false, default: true)]
    protected $backup_database;

    #[Column(label: 'Code', sortable: false)]
    #[Field(type: 'boolean', section: 'main', label: 'Backup code', required: false, default: false)]
    protected $backup_code;

    #[Column(label: 'Storage', sortable: false)]
    #[Field(type: 'boolean', section: 'main', label: 'Backup storage', required: false, default: false)]
    protected $backup_storage;

    #[Field(type: 'string', section: 'main', label: 'Cron expression', required: false)]
    protected $backup_cron_expression;

    #[Column(label: 'Max backups', sortable: true)]
    #[Field(type: 'number', section: 'main', label: 'Max backups', required: false, default: 10)]
    protected $max_backups;

    #[Column(label: 'Last status', sortable: true)]
    #[Field(type: 'string', section: 'status', label: 'Last status', required: false, disabled: true)]
    protected $last_status;

    #[Column(label: 'Last run at', sortable: true)]
    #[Field(type: 'datetime', section: 'status', label: 'Last run at', required: false, disabled: true)]
    protected $last_run_at;

    #[Field(type: 'text', section: 'status', label: 'Last error', required: false, disabled: true)]
    protected $last_error;

    protected $fillable = [
        'name',
        'backup_database',
        'backup_code',
        'backup_storage',
        'backup_cron_expression',
        'max_backups',
        'last_status',
        'last_run_at',
        'last_error',
    ];

    protected function casts(): array
    {
        return [
            'backup_database' => 'boolean',
            'backup_code' => 'boolean',
            'backup_storage' => 'boolean',
            'max_backups' => 'integer',
            'last_run_at' => 'datetime',
        ];
    }
}
