<?php

namespace App\Nexus\Modules\Demo\Models;

use App\Nexus\Modules\Demo\Enums\DemoStatus;
use App\Nexus\Modules\Demo\Requests\AdminStoreRequest;
use App\Nexus\Modules\Demo\Requests\AdminUpdateRequest;
use App\Nexus\Modules\Permission\Models\Permission;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
use Spatie\Translatable\HasTranslations;

/**
 * Showcase module for every field type/option that actually renders in the
 * Livewire admin form today — see resources/views/admin/docs.blade.php for
 * what each tab demonstrates and what's deliberately left out (types that
 * are documented on #[Field] but not yet ported to the Livewire pipeline).
 */
#[Module(
    name: 'demo',
    label: 'Demo',
    icon: 'solar:widget-bold',
    group: 'demo::translate.group_label',
    showInMenu: true,
    wizard: true,
    livewire: true,
)]
#[TableAction(name: 'edit', label: 'Edit', icon: 'edit', isConfirm: false)]
#[TableAction(name: 'delete', label: 'Delete', icon: 'delete', isConfirm: true)]
#[TableGroupAction(name: 'deleteGroup', fieldName: 'delete')]
#[Requests(store: AdminStoreRequest::class, update: AdminUpdateRequest::class)]
#[Section(name: 'text_inputs', column: 'basic_col', type: 'columns_2', icon: 'solar:text-bold', tab: 'basic')]
#[Section(name: 'toggles_dates', column: 'basic_col2', type: 'base', icon: 'solar:calendar-bold', tab: 'basic')]
#[Section(name: 'media', column: 'media_col', type: 'base', icon: 'solar:gallery-bold', tab: 'media')]
#[Section(name: 'choices', column: 'choices_col', type: 'base', icon: 'solar:list-bold', tab: 'choices')]
#[Section(name: 'relations', column: 'relations_col', type: 'base', icon: 'solar:link-bold', tab: 'relations')]
#[Section(name: 'repeater_sect', column: 'repeater_col', type: 'base', icon: 'solar:layers-bold', tab: 'repeater')]
#[Section(name: 'advanced', column: 'advanced_col', type: 'base', icon: 'solar:settings-bold', tab: 'advanced')]
class Demo extends Model
{
    use HasAttributeSchemaProperties, HasTranslations;

    // ---- Tab: basic ----

    #[Column(label: 'Title', sortable: true, searchable: true)]
    #[Field(type: 'string', section: 'text_inputs', label: 'Title', required: true, rules: ['max:255'], storeRules: ['required'])]
    protected $title;

    #[Field(type: 'string', section: 'text_inputs', label: 'Translated title', required: false, translated: true)]
    protected $title_i18n;

    #[Field(type: 'text', section: 'text_inputs', label: 'Description', required: false, editor: true)]
    protected $description;

    #[Column(label: 'Count', sortable: true)]
    #[Field(type: 'number', section: 'text_inputs', label: 'Count', required: false, rules: ['nullable', 'integer', 'min:0'])]
    protected $count;

    #[Column(label: 'Email')]
    #[Field(type: 'email', section: 'text_inputs', label: 'Email', required: false, storeRules: ['nullable', 'email', 'unique:demos,email'], updateRules: ['nullable', 'email'])]
    protected $email;

    #[Field(type: 'password', section: 'text_inputs', label: 'Secret', required: false, showInInfolist: false)]
    protected $secret;

    #[Column(label: 'Active', action: 'boolToggle', fieldName: 'is_active')]
    #[Field(type: 'boolean', section: 'toggles_dates', label: 'Is active', required: false)]
    protected $is_active;

    #[Field(type: 'date', section: 'toggles_dates', label: 'Event date', required: false)]
    protected $event_date;

    #[Field(type: 'datetime', section: 'toggles_dates', label: 'Event at', required: false)]
    protected $event_at;

    // ---- Tab: media ----

    #[Column(label: 'Image', customField: 'image')]
    #[Field(type: 'image', section: 'media', label: 'Image', required: false)]
    protected $image;

    #[Field(type: 'video', section: 'media', label: 'Video', required: false)]
    protected $video;

    #[Field(type: 'images', section: 'media', label: 'Gallery images', required: false)]
    protected $images;

    #[Field(type: 'videos', section: 'media', label: 'Gallery videos', required: false)]
    protected $videos;

    // ---- Tab: choices ----

    #[Column(label: 'Status')]
    #[Field(type: 'enum', section: 'choices', label: 'Status', required: false, enum: DemoStatus::class, default: 'draft')]
    protected $status;

    #[Column(label: 'Priority')]
    #[Field(type: 'select', section: 'choices', label: 'Priority', required: false)]
    protected $priority;

    // ---- Tab: relations (all on the real `permissions` table) ----

    #[Field(type: 'relation', section: 'relations', label: 'Permissions (preload)', required: false)]
    #[Relation(type: 'belongsToMany', show: 'display_name', showFallback: 'name')]
    public function permissionsPreload(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'demo_permissions_preload', 'demo_id', 'permission_id');
    }

    #[Field(type: 'relation', section: 'relations', label: 'Permissions (ajax search only)', required: false)]
    #[Relation(type: 'belongsToMany', show: 'display_name', showFallback: 'name', ajax: true)]
    public function permissionsSearchOnly(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'demo_permissions_search', 'demo_id', 'permission_id');
    }

    #[Field(type: 'relation', section: 'relations', label: 'Permissions (ajax preload + search)', required: false)]
    #[Relation(type: 'belongsToMany', show: 'display_name', showFallback: 'name', ajax: true, ajaxMode: 'load')]
    public function permissionsLoadMode(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'demo_permissions_load', 'demo_id', 'permission_id');
    }

    #[Field(type: 'relation', section: 'relations', label: 'Primary permission', required: false)]
    #[Relation(type: 'belongsTo', show: 'display_name', showFallback: 'name')]
    public function primaryPermission(): BelongsTo
    {
        return $this->belongsTo(Permission::class, 'primary_permission_id');
    }

    #[Field(type: 'relationManager', section: 'relations', label: 'Permissions (preload) — list', required: false)]
    #[Relation(show: 'display_name', showFallback: 'name', relatedModule: 'permission')]
    public function permissionsList(): BelongsToMany
    {
        return $this->permissionsPreload();
    }

    // ---- Tab: repeater ----

    #[Field(type: 'repeater', section: 'repeater_sect', label: 'Items', required: false)]
    #[Relation(type: 'hasMany')]
    #[RepeaterField(name: 'title', type: 'string', label: 'Title', required: true)]
    #[RepeaterField(name: 'qty', type: 'number', label: 'Qty', required: false, width: '120px')]
    public function items(): HasMany
    {
        return $this->hasMany(DemoItem::class);
    }

    // ---- Tab: advanced ----

    #[Column(label: 'Rating', customField: 'rating')]
    #[Field(type: 'ratingStars', section: 'advanced', label: 'Rating', required: false)]
    protected $rating;

    #[Field(type: 'phone', section: 'advanced', label: 'Phone (overridden view)', required: false)]
    protected $phone;

    #[Field(type: 'view', section: 'advanced', label: 'Price preview', required: false, view: 'demo::admin.components.price-preview')]
    protected $price_preview;

    #[Field(type: 'boolean', section: 'advanced', label: 'Show extra field', required: false)]
    protected $show_extra;

    #[Field(type: 'string', section: 'advanced', label: 'Extra field', required: false, showWhen: [['field' => 'show_extra', 'op' => 'truthy']], clearWhenHidden: true)]
    protected $extra_field;

    #[Field(type: 'json', section: 'advanced', label: 'Notes (read-only json)', required: false, showInInfolist: true)]
    protected $notes_json;

    #[Field(type: 'string', section: 'advanced', label: 'Api only note', required: false, apiExpose: true)]
    protected $api_only_note;

    #[Field(type: 'string', section: 'advanced', label: 'Locked after create', required: false, disabled: 'edit')]
    protected $locked_field;

    public $translatable = ['title_i18n'];

    protected $fillable = [
        'title', 'title_i18n', 'description', 'count', 'email', 'secret', 'is_active',
        'event_date', 'event_at', 'image', 'video', 'images', 'videos', 'status', 'priority',
        'primary_permission_id', 'rating', 'phone', 'show_extra', 'extra_field',
        'notes_json', 'api_only_note', 'locked_field',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'show_extra' => 'boolean',
        'event_date' => 'date',
        'event_at' => 'datetime',
        'notes_json' => 'array',
        'images' => 'array',
        'videos' => 'array',
    ];
}
