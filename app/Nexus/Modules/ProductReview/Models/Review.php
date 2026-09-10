<?php

namespace App\Nexus\Modules\ProductReview\Models;

use App\Models\User;
use App\Nexus\Modules\ProductReview\Observers\ReviewObserver;
use App\Nexus\Modules\ProductReview\Requests\AdminStoreRequest;
use App\Nexus\Modules\ProductReview\Requests\AdminUpdateRequest;
use App\Nexus\Modules\ShopProduct\Models\ShopProduct;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
 * Simplified from ZentaraStartProject's Review: dropped BelongsToShop
 * (single-shop-per-install) and SoftDeletes (matching every other simplified
 * module in this port). `images`/`comments`/`reactions` are satellite plain
 * models (ReviewImage/ReviewComment/ReviewReaction, not their own #[Module])
 * owned by this one — review comments always auto-publish on creation (no
 * moderation queue in the source project either), so unlike ProductQuestion
 * there's no real admin moderation need to build a UI for.
 */
#[Module(
    name: 'productReview',
    label: 'Product Reviews',
    icon: 'solar:star-bold',
    group: 'Shop',
    showInMenu: true,
    livewire: true,
)]
#[TableAction(name: 'edit', label: 'Edit', icon: 'edit', isConfirm: false)]
#[TableAction(name: 'delete', label: 'Delete', icon: 'delete', isConfirm: true)]
#[TableGroupAction(name: 'deleteGroup', fieldName: 'delete')]
#[TableFilter(name: 'search', label: 'Search by comment', type: 'search')]
#[Requests(actions: ['store' => AdminStoreRequest::class, 'update' => AdminUpdateRequest::class])]
#[Section(name: 'main', column: 'left', type: 'columns_2', icon: 'solar:star-bold')]
#[Section(name: 'settings', column: 'right', type: 'base', icon: 'solar:settings-bold')]
#[ObservedBy([ReviewObserver::class])]
class Review extends Model
{
    use HasAttributeSchemaProperties;

    #[Field(type: 'string', section: 'main', label: 'User name', required: false, rules: ['max:255'])]
    protected $user_name;

    #[Field(type: 'relation', section: 'main', label: 'User', required: false)]
    #[Relation(type: 'belongsTo', show: 'email', ajax: true)]
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    #[Column(label: 'Product', sortable: true)]
    #[Field(type: 'relation', section: 'main', label: 'Product', required: true)]
    #[Relation(type: 'belongsTo', show: 'name', ajax: true)]
    public function product(): BelongsTo
    {
        return $this->belongsTo(ShopProduct::class);
    }

    #[Column(label: 'Rating', sortable: true)]
    #[Field(type: 'number', section: 'main', label: 'Rating', required: true, rules: ['integer', 'min:1', 'max:5'])]
    protected $rating;

    #[Column(label: 'Comment', searchable: true)]
    #[Field(type: 'text', section: 'main', label: 'Comment', required: false)]
    protected $comment;

    #[Field(type: 'string', section: 'main', label: 'Advantages', required: false, rules: ['max:255'])]
    protected $advantages;

    #[Field(type: 'string', section: 'main', label: 'Disadvantages', required: false, rules: ['max:255'])]
    protected $disadvantages;

    #[Field(type: 'url', section: 'main', label: 'Video', required: false, rules: ['max:255'])]
    protected $video;

    #[Column(label: 'Publish', action: 'boolToggle', fieldName: 'publish')]
    #[Field(type: 'boolean', section: 'settings', label: 'Publish', required: false, default: false)]
    protected $publish;

    protected $fillable = [
        'user_name', 'user_id', 'product_id', 'publish', 'comment', 'advantages', 'disadvantages', 'rating', 'video',
    ];

    protected $casts = [
        'publish' => 'boolean',
        'rating' => 'integer',
    ];

    public function scopePublish(Builder $query): Builder
    {
        return $query->where('reviews.publish', true);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ReviewImage::class);
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(ReviewReaction::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ReviewComment::class)->whereNull('parent_id');
    }
}
