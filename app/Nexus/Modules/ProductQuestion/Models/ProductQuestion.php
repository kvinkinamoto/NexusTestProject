<?php

namespace App\Nexus\Modules\ProductQuestion\Models;

use App\Models\User;
use App\Nexus\Modules\ProductQuestion\Requests\AdminStoreRequest;
use App\Nexus\Modules\ProductQuestion\Requests\AdminUpdateRequest;
use App\Nexus\Modules\ShopProduct\Models\ShopProduct;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Kalnoy\Nestedset\NodeTrait;
use Nodex\Nexus\Attributes\Column;
use Nodex\Nexus\Attributes\Field;
use Nodex\Nexus\Attributes\Module;
use Nodex\Nexus\Attributes\Relation;
use Nodex\Nexus\Attributes\Requests;
use Nodex\Nexus\Attributes\Section;
use Nodex\Nexus\Attributes\TableAction;
use Nodex\Nexus\Attributes\TableFilter;
use Nodex\Nexus\Attributes\TableGroupAction;
use Nodex\Nexus\Concerns\HasAttributeSchemaProperties;

/**
 * Simplified from ZentaraStartProject's ProductQuestion: dropped
 * BelongsToShop (single-shop-per-install) and the bespoke "replies" nested
 * admin field type — a reply is just another row of this same self-joining
 * table (parent_id set), so it's manageable through the normal module table
 * like any other row, same as ShopCategory's own parent/child tree.
 */
#[Module(
    name: 'productQuestion',
    label: 'Product Questions',
    icon: 'solar:chat-round-dots-bold',
    group: 'Shop',
    showInMenu: false,
    livewire: true,
)]
#[TableAction(name: 'edit', label: 'Edit', icon: 'edit', isConfirm: false)]
#[TableAction(name: 'delete', label: 'Delete', icon: 'delete', isConfirm: true)]
#[TableGroupAction(name: 'deleteGroup', fieldName: 'delete')]
#[TableFilter(name: 'search', label: 'Search by text', type: 'search')]
#[Requests(actions: ['store' => AdminStoreRequest::class, 'update' => AdminUpdateRequest::class])]
#[Section(name: 'main', column: 'left', type: 'columns_2', icon: 'solar:chat-round-dots-bold')]
#[Section(name: 'settings', column: 'right', type: 'base', icon: 'solar:settings-bold')]
class ProductQuestion extends Model
{
    use HasAttributeSchemaProperties, NodeTrait;

    #[Column(label: 'Product', sortable: true)]
    #[Field(type: 'relation', section: 'main', label: 'Product', required: true)]
    #[Relation(type: 'belongsTo', show: 'name', ajax: true)]
    public function product(): BelongsTo
    {
        return $this->belongsTo(ShopProduct::class);
    }

    #[Field(type: 'relation', section: 'main', label: 'User', required: false)]
    #[Relation(type: 'belongsTo', show: 'email', ajax: true)]
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    #[Field(type: 'string', section: 'main', label: 'Guest name', required: false, rules: ['max:255'])]
    protected $guest_name;

    #[Field(type: 'email', section: 'main', label: 'Guest email', required: false)]
    protected $guest_email;

    #[Column(label: 'Text', searchable: true)]
    #[Field(type: 'text', section: 'main', label: 'Text', required: true, rules: ['max:1000'])]
    protected $text;

    #[Field(type: 'relation', section: 'settings', label: 'Parent question', required: false)]
    #[Relation(type: 'belongsTo', show: 'text')]
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    #[Column(label: 'Publish', action: 'boolToggle', fieldName: 'publish')]
    #[Field(type: 'boolean', section: 'settings', label: 'Publish', required: false, default: false)]
    protected $publish;

    protected $fillable = [
        'product_id', 'user_id', 'guest_name', 'guest_email', 'parent_id', 'text', 'publish',
    ];

    protected $casts = [
        'publish' => 'boolean',
    ];

    public function scopePublish(Builder $query): Builder
    {
        return $query->where('product_questions.publish', true);
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(QuestionReaction::class, 'question_id');
    }

    public function getLikesCountAttribute(): int
    {
        return $this->reactions->where('value', 1)->count();
    }

    public function getDislikesCountAttribute(): int
    {
        return $this->reactions->where('value', -1)->count();
    }
}
