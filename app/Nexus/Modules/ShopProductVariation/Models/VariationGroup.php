<?php

namespace App\Nexus\Modules\ShopProductVariation\Models;

use App\Nexus\Modules\ShopAttribute\Models\ShopAttribute;
use App\Nexus\Modules\ShopProduct\Models\ShopProduct;
use App\Nexus\Modules\ShopProductVariation\Requests\AdminStoreRequest;
use App\Nexus\Modules\ShopProductVariation\Requests\AdminUpdateRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Nodex\Nexus\Attributes\Field;
use Nodex\Nexus\Attributes\Module;
use Nodex\Nexus\Attributes\Relation;
use Nodex\Nexus\Attributes\Requests;
use Nodex\Nexus\Attributes\Section;
use Nodex\Nexus\Attributes\TableAction;
use Nodex\Nexus\Attributes\TableGroupAction;
use Nodex\Nexus\Concerns\HasAttributeSchemaProperties;

/**
 * Simplified from ZentaraStartProject's VariationGroup: the original let you
 * bulk-assign member products from the group's own edit screen (a
 * multi-select "products" field backed by SyncProductsAction, cross-
 * validated so a product couldn't land in two groups at once). Here the
 * relationship is set from the Product side instead — ShopProduct gained its
 * own `variationGroup` belongsTo dropdown (see the additive migration in
 * this module) — which is a plain foreign key, so "already in another
 * group" simply can't happen: no bulk picker, no conflict-validation trait
 * needed. This module itself is left holding only which attributes
 * distinguish the group (e.g. Color+Size) plus a read-only list of its
 * current member products.
 */
#[Module(
    name: 'shopProductVariation',
    label: 'Variation Groups',
    icon: 'solar:layers-bold',
    group: 'Shop',
    showInMenu: true,
    livewire: true,
)]
#[TableAction(name: 'edit', label: 'Edit', icon: 'edit', isConfirm: false)]
#[TableAction(name: 'delete', label: 'Delete', icon: 'delete', isConfirm: true)]
#[TableGroupAction(name: 'deleteGroup', fieldName: 'delete')]
#[Requests(actions: ['store' => AdminStoreRequest::class, 'update' => AdminUpdateRequest::class])]
#[Section(name: 'main', column: 'left', type: 'base', icon: 'solar:layers-bold')]
#[Section(name: 'products_section', column: 'left', type: 'base', icon: 'solar:box-bold')]
class VariationGroup extends Model
{
    use HasAttributeSchemaProperties;

    protected $table = 'variation_groups';

    #[Field(type: 'relation', section: 'main', label: 'Distinguishing attributes', required: true)]
    #[Relation(type: 'belongsToMany', show: 'name', ajax: true)]
    public function variationAttributes(): BelongsToMany
    {
        return $this->belongsToMany(ShopAttribute::class, 'variation_group_attributes', 'variation_group_id', 'attribute_id')->withTimestamps();
    }

    #[Field(type: 'relationManager', section: 'products_section', label: 'Products in this group', required: false)]
    #[Relation(show: 'name', relatedModule: 'shopProduct')]
    public function products(): HasMany
    {
        return $this->hasMany(ShopProduct::class, 'variation_group_id');
    }

    protected $fillable = [];
}
