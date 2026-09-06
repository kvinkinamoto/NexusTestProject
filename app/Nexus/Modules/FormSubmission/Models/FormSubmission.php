<?php

namespace App\Nexus\Modules\FormSubmission\Models;

use App\Nexus\Modules\FormSubmission\Requests\AdminStoreRequest;
use App\Nexus\Modules\FormSubmission\Requests\AdminUpdateRequest;
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
use Nodex\Nexus\Modules\Form\Models\Form;

/**
 * Read-only from the admin's point of view — rows are created exclusively by
 * FormSubmissionController::store() on the public side. Every field below is
 * disabled: true; admins can view and delete, not edit.
 */
#[Module(
    name: 'formSubmission',
    label: 'Заявки з форм',
    icon: 'solar:inbox-bold',
    group: 'Content',
    showInMenu: true,
    livewire: true,
)]
#[Requests(actions: ['store' => AdminStoreRequest::class, 'update' => AdminUpdateRequest::class])]
#[TableAction(name: 'edit', label: 'View', icon: 'edit', isConfirm: false)]
#[TableAction(name: 'delete', label: 'Delete', icon: 'delete', isConfirm: true)]
#[TableGroupAction(name: 'deleteGroup', fieldName: 'delete')]
#[Section(name: 'main', column: 'right', type: 'base', icon: 'solar:inbox-bold')]
class FormSubmission extends Model
{
    use HasAttributeSchemaProperties;

    #[Column(label: 'Form', sortable: true)]
    #[Field(type: 'relation', section: 'main', label: 'Form', required: false, disabled: true)]
    #[Relation(type: 'belongsTo', show: 'title')]
    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    #[Field(type: 'json', section: 'main', label: 'Submitted data', required: false, disabled: true)]
    protected $data;

    #[Column(label: 'IP address')]
    #[Field(type: 'string', section: 'main', label: 'IP address', required: false, disabled: true)]
    protected $ip_address;

    #[Column(label: 'Submitted at', sortable: true)]
    protected $created_at;

    protected $fillable = [
        'form_id', 'data', 'ip_address',
    ];

    protected $casts = [
        'data' => 'array',
    ];
}
