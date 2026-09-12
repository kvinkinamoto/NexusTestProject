<?php

namespace App\Nexus\Modules\Wishlist\Models;

use App\Models\User;
use App\Nexus\Modules\ShopProduct\Models\ShopProduct;
use App\Nexus\Modules\Wishlist\Requests\AdminStoreRequest;
use App\Nexus\Modules\Wishlist\Requests\AdminUpdateRequest;
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
 * Simplified from ZentaraStartProject's Wishlist: the old model was
 * polymorphic (wishlistable_type/wishlistable_id, covering products, blog
 * posts, and blog categories). Nexus's BlogPost/BlogCategory don't share a
 * common "publish" scope with ShopProduct (is_published vs publish vs no
 * concept at all), so a generic morph-based query would need per-type branch
 * logic for no real benefit — wishlisting a blog post/category is a rare
 * feature blog readers rarely use, and this is a paid e-commerce module.
 * Scoped down to products only: a plain `product_id` FK, no morph.
 */
#[Module(
    name: 'wishlist',
    label: 'Wishlist',
    icon: 'solar:heart-bold',
    group: 'Shop',
    showInMenu: true,
    livewire: true,
)]
#[TableAction(name: 'edit', label: 'Edit', icon: 'edit', isConfirm: false)]
#[TableAction(name: 'delete', label: 'Delete', icon: 'delete', isConfirm: true)]
#[TableGroupAction(name: 'deleteGroup', fieldName: 'delete')]
#[Requests(actions: ['store' => AdminStoreRequest::class, 'update' => AdminUpdateRequest::class])]
#[Section(name: 'main', column: 'left', type: 'base', icon: 'solar:heart-bold')]
class WishlistItem extends Model
{
    use HasAttributeSchemaProperties;

    #[Column(label: 'User', sortable: true)]
    #[Field(type: 'relation', section: 'main', label: 'User', required: true)]
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

    protected $fillable = ['user_id', 'session_id', 'product_id'];
}
