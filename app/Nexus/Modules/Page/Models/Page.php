<?php

namespace App\Nexus\Modules\Page\Models;

use App\Nexus\Modules\Page\Requests\AdminStoreRequest;
use App\Nexus\Modules\Page\Requests\AdminUpdateRequest;
use App\Nexus\Modules\Page\Services\PageMenuResolver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Nodex\Nexus\Attributes\Field;
use Nodex\Nexus\Attributes\Module;
use Nodex\Nexus\Attributes\Relation;
use Nodex\Nexus\Attributes\Requests;
use Nodex\Nexus\Attributes\Section;
use Nodex\Nexus\Attributes\TableAction;
use Nodex\Nexus\Attributes\TableGroupAction;
use Nodex\Nexus\Concerns\HasAttributeSchemaProperties;
use Spatie\Translatable\HasTranslations;

#[Module(
    name: 'page',
    label: 'Сторінки',
    icon: 'solar:document-text-bold',
    group: 'Content',
    showInMenu: true,
    livewire: true,
    menuResolver: PageMenuResolver::class,
)]
#[Requests(actions: ['store' => AdminStoreRequest::class, 'update' => AdminUpdateRequest::class])]
#[TableAction(name: 'edit', label: 'Edit', icon: 'edit', isConfirm: false)]
#[TableAction(name: 'delete', label: 'Delete', icon: 'delete', isConfirm: true)]
#[TableGroupAction(name: 'deleteGroup', fieldName: 'delete')]
#[Section(name: 'main', column: 'right', type: 'columns_2', icon: 'solar:document-text-bold')]
#[Section(name: 'seo_section', column: 'right', type: 'base', icon: 'solar:magnifer-bold')]
#[Section(name: 'blocks_section', column: 'left', type: 'base', icon: 'solar:widget-bold')]
class Page extends Model
{
    use HasAttributeSchemaProperties, HasTranslations;

    #[Field(type: 'string', section: 'main', label: 'Title', required: true, rules: ['max:255'], apiExpose: true)]
    protected $title;

    #[Field(type: 'slug', section: 'main', label: 'Slug', required: false, slugSource: 'title', apiExpose: true)]
    protected $slug;

    #[Field(type: 'string', section: 'seo_section', label: 'Meta title', required: false, rules: ['max:255'], translated: true, apiExpose: true)]
    protected $meta_title;

    #[Field(type: 'text', section: 'seo_section', label: 'Meta description', required: false, translated: true, apiExpose: true)]
    protected $meta_description;

    #[Field(type: 'boolean', section: 'main', label: 'Is active', required: false, default: true, apiExpose: true)]
    protected $is_active;

    /**
     * Ordered list of heterogeneous content blocks — each row's own field
     * set comes from BlockTypeRegistry::find($row['type']) at render time,
     * not from a fixed #[RepeaterField] column list (see
     * ManagesBlockFields' docblock for why this can't be a plain repeater).
     */
    #[Field(type: 'blockEditor', section: 'blocks_section', label: 'Blocks', required: false)]
    #[Relation(type: 'hasMany')]
    public function blocks(): HasMany
    {
        return $this->hasMany(PageBlock::class)->orderBy('position');
    }

    /**
     * Lets Nodex\Nexus\Services\RelatedEntityFieldService::getEntities()'s
     * generic "is this content published?" auto-detection (which only
     * checks for a scopeIsPublished() method or an is_published column)
     * apply to Page too — Page's own publish flag is named is_active.
     */
    public function scopeIsPublished(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public $translatable = ['meta_title', 'meta_description'];

    protected $fillable = [
        'title', 'slug', 'meta_title', 'meta_description', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
