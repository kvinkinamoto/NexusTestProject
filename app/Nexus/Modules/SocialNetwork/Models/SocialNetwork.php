<?php

namespace App\Nexus\Modules\SocialNetwork\Models;

use App\Nexus\Modules\SocialNetwork\Enums\SocialPlatformEnum;
use App\Nexus\Modules\SocialNetwork\Requests\AdminStoreRequest;
use App\Nexus\Modules\SocialNetwork\Requests\AdminUpdateRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Kalnoy\Nestedset\NodeTrait;
use Nodex\Nexus\Attributes\Column;
use Nodex\Nexus\Attributes\Field;
use Nodex\Nexus\Attributes\Module;
use Nodex\Nexus\Attributes\Relation;
use Nodex\Nexus\Attributes\Requests;
use Nodex\Nexus\Attributes\Section;
use Nodex\Nexus\Attributes\TableAction;
use Nodex\Nexus\Attributes\TableGroupAction;
use Nodex\Nexus\Concerns\HasAttributeSchemaProperties;
use Spatie\Translatable\HasTranslations;

/**
 * Simplified from ZentaraStartProject's SocialNetwork: dropped SoftDeletes
 * (matches every other simplified module in this port) and the
 * `access_token`/`refresh_token`/`token_expires_at`/`external_id` columns —
 * confirmed dead even in the old code: Facebook's page token lives in
 * Cache (FacebookAuthService), Instagram/TikTok's in the separate
 * SocialToken table: nothing ever reads/writes these columns on this model.
 * Parent/child tree kept (NodeTrait) — lets admins group entries (e.g. a
 * "Follow us" parent with per-platform children), matching the old intent.
 */
#[Module(
    name: 'socialNetwork',
    label: 'Social Networks',
    icon: 'solar:share-circle-bold',
    group: 'Site',
    showInMenu: true,
    livewire: true,
)]
#[TableAction(name: 'edit', label: 'Edit', icon: 'edit', isConfirm: false)]
#[TableAction(name: 'delete', label: 'Delete', icon: 'delete', isConfirm: true)]
#[TableGroupAction(name: 'deleteGroup', fieldName: 'delete')]
#[Requests(actions: ['store' => AdminStoreRequest::class, 'update' => AdminUpdateRequest::class])]
#[Section(name: 'main', column: 'left', type: 'columns_2', icon: 'solar:share-circle-bold')]
#[Section(name: 'settings', column: 'right', type: 'base', icon: 'solar:settings-bold')]
#[Section(name: 'relations', column: 'right', type: 'base', icon: 'solar:folder-bold')]
class SocialNetwork extends Model
{
    use HasAttributeSchemaProperties, HasTranslations, NodeTrait;

    #[Column(label: 'Title', sortable: true)]
    #[Field(type: 'string', section: 'main', label: 'Title', required: false, translated: true)]
    protected $title;

    #[Field(type: 'string', section: 'main', label: 'Caption', required: false, translated: true)]
    protected $caption;

    #[Column(label: 'URL', sortable: true)]
    #[Field(type: 'string', section: 'main', label: 'URL', required: true, rules: ['max:255'])]
    protected $url;

    #[Column(label: 'Logo', customField: 'showImage')]
    #[Field(type: 'image', section: 'main', label: 'Logo', required: false)]
    protected $logo;

    #[Field(type: 'string', section: 'settings', label: 'Key', required: false, rules: ['max:255'])]
    protected $key;

    #[Column(label: 'Platform', sortable: true)]
    #[Field(type: 'enum', section: 'settings', label: 'Platform', required: false, enum: SocialPlatformEnum::class)]
    protected $platform;

    #[Field(type: 'boolean', section: 'settings', label: 'Sync posts', required: false, default: false)]
    protected $sync_posts;

    #[Column(label: 'Publish', action: 'boolToggle', fieldName: 'publish')]
    #[Field(type: 'boolean', section: 'settings', label: 'Publish', required: false, default: true)]
    protected $publish;

    #[Field(type: 'relation', section: 'relations', label: 'Parent', required: false)]
    #[Relation(type: 'belongsTo', show: 'url')]
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public $translatable = ['title', 'caption'];

    protected $fillable = [
        'title', 'caption', 'url', 'logo', 'key', 'platform', 'sync_posts', 'publish', 'parent_id',
    ];

    protected $casts = [
        'sync_posts' => 'boolean',
        'publish' => 'boolean',
        'platform' => SocialPlatformEnum::class,
    ];

    public function scopePublish(Builder $query): Builder
    {
        return $query->where('social_networks.publish', true);
    }
}
