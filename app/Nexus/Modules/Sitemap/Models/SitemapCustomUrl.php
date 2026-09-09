<?php

namespace App\Nexus\Modules\Sitemap\Models;

use App\Nexus\Modules\Sitemap\Enums\SitemapChangeFrequency;
use App\Nexus\Modules\Sitemap\Requests\AdminStoreRequest;
use App\Nexus\Modules\Sitemap\Requests\AdminUpdateRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Nodex\Nexus\Attributes\Column;
use Nodex\Nexus\Attributes\Field;
use Nodex\Nexus\Attributes\Module;
use Nodex\Nexus\Attributes\Requests;
use Nodex\Nexus\Attributes\Section;
use Nodex\Nexus\Attributes\Setting;
use Nodex\Nexus\Attributes\TableAction;
use Nodex\Nexus\Attributes\TableGroupAction;
use Nodex\Nexus\Concerns\HasAttributeSchemaProperties;

/**
 * Admin-manageable extra sitemap entries — URLs with no module of their own
 * (an external landing page, a legacy path kept for SEO) that should still
 * appear in the generated sitemap. Auto-discovered generators (one per
 * module, see Generators/AbstractSitemapGenerator) cover everything else;
 * see Services/SitemapAutoService::discoverGenerators().
 *
 * Also carries the module's #[Setting]s (mode/base_url/split_size) — same
 * "one module, bound model + settings" shape as App\Nexus\Modules\Demo.
 */
#[Module(
    name: 'sitemap',
    label: 'Sitemap',
    icon: 'solar:map-point-wave-bold',
    group: 'Site',
    showInMenu: true,
    livewire: true,
)]
#[TableAction(name: 'edit', label: 'Edit', icon: 'edit', isConfirm: false)]
#[TableAction(name: 'delete', label: 'Delete', icon: 'delete', isConfirm: true)]
#[TableGroupAction(name: 'deleteGroup', fieldName: 'delete')]
#[Requests(actions: ['store' => AdminStoreRequest::class, 'update' => AdminUpdateRequest::class])]
#[Section(name: 'main', column: 'right', type: 'base', icon: 'solar:map-point-wave-bold')]
#[Setting(name: 'mode', type: 'select', label: 'Generation mode', default: 'multi', options: ['multi' => 'Multi file + index', 'single' => 'Single file'], required: true)]
#[Setting(name: 'base_url', type: 'string', label: 'Base URL override', comment: 'Overrides APP_URL for every generated URL. Leave blank to use APP_URL.')]
#[Setting(name: 'split_size', type: 'integer', label: 'Split size', default: 0, comment: 'Max URLs per file before splitting into chunks + a sub-index. 0 = off.')]
class SitemapCustomUrl extends Model
{
    use HasAttributeSchemaProperties;

    protected $table = 'sitemap_custom_urls';

    #[Column(label: 'URL', sortable: true, searchable: true)]
    #[Field(type: 'string', section: 'main', label: 'URL', required: true, rules: ['max:2048'])]
    protected $url;

    #[Column(label: 'Priority')]
    #[Field(type: 'number', section: 'main', label: 'Priority', required: false, rules: ['nullable', 'numeric', 'between:0,1'], default: 0.5)]
    protected $priority;

    #[Column(label: 'Frequency')]
    #[Field(type: 'enum', section: 'main', label: 'Change frequency', required: false, enum: SitemapChangeFrequency::class, default: 'monthly')]
    protected $changefreq;

    #[Field(type: 'datetime', section: 'main', label: 'Last modified', required: false)]
    protected $lastmod;

    #[Column(label: 'Active', action: 'boolToggle', fieldName: 'is_active')]
    #[Field(type: 'boolean', section: 'main', label: 'Is active', required: false, default: true)]
    protected $is_active;

    #[Field(type: 'string', section: 'main', label: 'Note', required: false, rules: ['max:500'])]
    protected $note;

    protected $fillable = [
        'url', 'priority', 'changefreq', 'lastmod', 'is_active', 'note',
    ];

    protected $casts = [
        'priority' => 'float',
        'is_active' => 'boolean',
        'lastmod' => 'datetime',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
