<?php

namespace App\Nexus\Modules\PaymentMethod\Models;

use App\Nexus\Modules\PaymentMethod\Requests\AdminStoreRequest;
use App\Nexus\Modules\PaymentMethod\Requests\AdminUpdateRequest;
use Illuminate\Database\Eloquent\Model;
use Nodex\Nexus\Attributes\Column;
use Nodex\Nexus\Attributes\Field;
use Nodex\Nexus\Attributes\Module;
use Nodex\Nexus\Attributes\Requests;
use Nodex\Nexus\Attributes\Section;
use Nodex\Nexus\Attributes\TableAction;
use Nodex\Nexus\Attributes\TableFilter;
use Nodex\Nexus\Attributes\TableGroupAction;
use Nodex\Nexus\Concerns\HasAttributeSchemaProperties;
use Spatie\Translatable\HasTranslations;

/**
 * `code` is what `Order::payment_method`/`PaymentManager::driver()` match
 * against — it identifies the driver (or, for offline methods like
 * cash-on-delivery/bank transfer, nothing, since those never call a driver).
 * `provider` is the underlying vendor label (liqpay/monobank/privatbank/
 * offline) used for admin filtering; `type` is the checkout-grouping
 * category (gateway/installment/offline) `PaymentMethodService` groups by.
 * All three stay plain strings, matching ZentaraStartProject exactly — new
 * offline methods need to be addable from the admin without a code change.
 */
#[Module(
    name: 'paymentMethod',
    label: 'Payment Methods',
    icon: 'solar:card-bold',
    group: 'Shop',
    showInMenu: true,
    livewire: true,
)]
#[TableAction(name: 'edit', label: 'Edit', icon: 'edit', isConfirm: false)]
#[TableAction(name: 'delete', label: 'Delete', icon: 'delete', isConfirm: true)]
#[TableGroupAction(name: 'deleteGroup', fieldName: 'delete')]
#[TableFilter(name: 'search', label: 'Search by title or code', type: 'search')]
#[Requests(actions: ['store' => AdminStoreRequest::class, 'update' => AdminUpdateRequest::class])]
#[Section(name: 'information', column: 'left', type: 'columns_2', icon: 'solar:card-bold')]
#[Section(name: 'configuration', column: 'right', type: 'base', icon: 'solar:settings-bold')]
class PaymentMethod extends Model
{
    use HasAttributeSchemaProperties, HasTranslations;

    #[Column(label: 'Title', sortable: true, searchable: true)]
    #[Field(type: 'string', section: 'information', label: 'Title', required: true, translated: true)]
    protected $title;

    #[Field(type: 'text', section: 'information', label: 'Description', required: false, translated: true)]
    protected $description;

    #[Column(label: 'Code', sortable: true, searchable: true)]
    #[Field(type: 'string', section: 'configuration', label: 'Code', required: true, rules: ['max:64'])]
    protected $code;

    #[Column(label: 'Provider', sortable: true)]
    #[Field(type: 'string', section: 'configuration', label: 'Provider', required: true, rules: ['max:64'])]
    protected $provider;

    #[Column(label: 'Type')]
    #[Field(type: 'string', section: 'configuration', label: 'Type', required: true, rules: ['max:64'])]
    protected $type;

    #[Field(type: 'number', section: 'configuration', label: 'Installment parts count', required: false, rules: ['nullable', 'integer', 'min:2'])]
    protected $count_installment;

    #[Column(label: 'Publish', action: 'boolToggle', fieldName: 'publish')]
    #[Field(type: 'boolean', section: 'configuration', label: 'Publish', required: false, default: true)]
    protected $publish;

    public $translatable = ['title', 'description'];

    protected $fillable = ['code', 'type', 'provider', 'title', 'description', 'publish', 'count_installment'];

    protected $casts = [
        'publish' => 'boolean',
        'count_installment' => 'integer',
    ];

    public function scopePublish($query)
    {
        return $query->where('publish', true);
    }
}
