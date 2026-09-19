<?php

namespace App\Nexus\Modules\ProductRelease\Models;

use App\Nexus\Modules\ProductRelease\Requests\AdminStoreRequest;
use App\Nexus\Modules\ProductRelease\Requests\AdminUpdateRequest;
use App\Nexus\Modules\ShopProduct\Models\ShopProduct;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Nodex\Nexus\Attributes\Column;
use Nodex\Nexus\Attributes\Field;
use Nodex\Nexus\Attributes\Module;
use Nodex\Nexus\Attributes\Permission;
use Nodex\Nexus\Attributes\Relation;
use Nodex\Nexus\Attributes\Requests;
use Nodex\Nexus\Attributes\Section;
use Nodex\Nexus\Attributes\TableAction;
use Nodex\Nexus\Attributes\TableFilter;
use Nodex\Nexus\Attributes\TableGroupAction;
use Nodex\Nexus\Concerns\HasAttributeSchemaProperties;

/**
 * `zip_disk_path`/`zip_size`/`synced_at` are plain DB columns with no
 * #[Field] — filled only by GithubReleaseSyncService (from a GitHub tag) or
 * the `uploadRelease` admin action (from a manually uploaded .zip,
 * `Http\Controllers\AdminController::uploadRelease`, for sellers who'd
 * rather not set up a GitHub token/webhook per repo at all), never directly
 * editable from the standard admin form (see MODULE-SALES-SITE-PLAN.md
 * section 2.2).
 */
#[Module(
    name: 'productRelease',
    label: 'Версія продукту',
    icon: 'solar:tag-bold',
    group: 'Shop',
    showInMenu: true,
    livewire: true,
    requires: ['ShopProduct'],
)]
#[TableAction(name: 'edit', label: 'Edit', icon: 'edit', isConfirm: false)]
#[TableAction(name: 'delete', label: 'Delete', icon: 'delete', isConfirm: true)]
#[TableAction(name: 'syncFromGithub', label: 'Синхронізувати з GitHub', icon: 'refresh', isConfirm: false)]
#[TableAction(name: 'uploadRelease', label: 'Завантажити реліз', icon: 'upload', isConfirm: false, isMain: true)]
#[TableGroupAction(name: 'deleteGroup', fieldName: 'delete')]
#[TableFilter(name: 'search', label: 'Пошук за версією', type: 'search')]
#[Permission(action: 'uploadRelease', label: 'Upload release archive')]
#[Requests(actions: ['store' => AdminStoreRequest::class, 'update' => AdminUpdateRequest::class])]
#[Section(name: 'main', column: 'right', type: 'base', icon: 'solar:tag-bold')]
class ProductRelease extends Model
{
    use HasAttributeSchemaProperties;

    #[Field(type: 'relation', section: 'main', label: 'Продукт', required: true)]
    #[Relation(type: 'belongsTo', show: 'name')]
    public function product(): BelongsTo
    {
        return $this->belongsTo(ShopProduct::class, 'product_id');
    }

    #[Column(label: 'Версія', sortable: true, searchable: true)]
    #[Field(type: 'string', section: 'main', label: 'Версія', required: true, rules: ['max:50'])]
    protected $version;

    #[Column(label: 'GitHub-тег', searchable: true)]
    #[Field(type: 'string', section: 'main', label: 'GitHub-тег', required: true, disabled: 'edit', rules: ['max:100'])]
    protected $github_tag;

    #[Field(type: 'markdown', section: 'main', label: 'Changelog', required: false)]
    protected $changelog;

    #[Field(type: 'string', section: 'main', label: 'Мін. версія ядра', required: false, rules: ['max:20'])]
    protected $min_core_version;

    #[Field(type: 'string', section: 'main', label: 'Макс. версія ядра', required: false, rules: ['max:20'])]
    protected $max_core_version;

    #[Column(label: 'Опубліковано', sortable: true)]
    #[Field(type: 'datetime', section: 'main', label: 'Опубліковано', required: false)]
    protected $published_at;

    /**
     * `zip_disk_path`/`zip_size`/`synced_at` are mass-assignable here for
     * GithubReleaseSyncService's own writes, but never admin-editable: the
     * generic store/update action intersects `$fillable` with the Request
     * class's `validated()` output (see StoreActionMethod::handle()), and
     * neither AdminStoreRequest nor AdminUpdateRequest declares a rule for
     * them — no #[Field] means no admin form input for them either.
     */
    protected $fillable = [
        'product_id', 'version', 'github_tag', 'changelog',
        'min_core_version', 'max_core_version', 'published_at',
        'zip_disk_path', 'zip_size', 'synced_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'synced_at' => 'datetime',
        'zip_size' => 'integer',
    ];

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }
}
