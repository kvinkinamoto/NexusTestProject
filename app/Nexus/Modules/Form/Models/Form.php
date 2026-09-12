<?php

namespace App\Nexus\Modules\Form\Models;

use App\Nexus\Modules\Form\Requests\AdminStoreRequest;
use App\Nexus\Modules\Form\Requests\AdminUpdateRequest;
use App\Nexus\Modules\FormSubmission\Models\FormSubmission;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Nodex\Nexus\Attributes\Column;
use Nodex\Nexus\Attributes\Field;
use Nodex\Nexus\Attributes\Module;
use Nodex\Nexus\Attributes\Relation;
use Nodex\Nexus\Attributes\RepeaterField;
use Nodex\Nexus\Attributes\Requests;
use Nodex\Nexus\Attributes\Section;
use Nodex\Nexus\Attributes\SectionColumn;
use Nodex\Nexus\Attributes\TableAction;
use Nodex\Nexus\Attributes\TableGroupAction;
use Nodex\Nexus\Concerns\HasAttributeSchemaProperties;

#[Module(
    name: 'form',
    label: 'Форми',
    icon: 'solar:document-add-bold',
    group: 'Content',
    showInMenu: true,
    livewire: true,
)]
#[SectionColumn('foolWith', '12')]
#[Requests(actions: ['store' => AdminStoreRequest::class, 'update' => AdminUpdateRequest::class])]
#[TableAction(name: 'edit', label: 'Edit', icon: 'edit', isConfirm: false)]
#[TableAction(name: 'delete', label: 'Delete', icon: 'delete', isConfirm: true)]
#[TableGroupAction(name: 'deleteGroup', fieldName: 'delete')]
#[Section(name: 'main', column: 'foolWith', type: 'columns_2', icon: 'solar:clipboard-list-bold')]
#[Section(name: 'fields_section', column: 'foolWith', type: 'base', icon: 'solar:layers-bold')]
#[Section(name: 'submissions_section', column: 'foolWith', type: 'base', icon: 'solar:inbox-bold')]
class Form extends Model
{
    use HasAttributeSchemaProperties;

    #[Column(label: 'Name', sortable: true, searchable: true)]
    #[Field(type: 'string', section: 'main', label: 'Name', required: true, rules: ['max:255'])]
    protected $name;

    #[Column(label: 'Title', sortable: true, searchable: true)]
    #[Field(type: 'string', section: 'main', label: 'Title', required: true, rules: ['max:255'])]
    protected $title;

    #[Field(type: 'slug', section: 'main', label: 'Slug', required: false, slugSource: 'title')]
    protected $slug;

    #[Field(type: 'text', section: 'main', label: 'Success message', required: false)]
    protected $success_message;

    #[Field(type: 'email', section: 'main', label: 'Notify email', required: false)]
    protected $notify_email;

    #[Column(label: 'Active', action: 'boolToggle', fieldName: 'is_active')]
    #[Field(type: 'boolean', section: 'main', label: 'Is active', required: false, default: true)]
    protected $is_active;

    /**
     * Field definitions edited inline as a repeater on the Form's own edit
     * screen — same mechanism as Demo::items()/DemoItem, no bespoke
     * drag-and-drop UI needed. 'type' is a plain string (validated against
     * FormFieldType in Requests/Form/*), not 'select', since #[RepeaterField]
     * has no #[Field(enum:)]-style fixed-choice option for a repeater column.
     */
    #[Field(type: 'repeater', section: 'fields_section', label: 'Fields', required: false)]
    #[Relation(type: 'hasMany')]
    #[RepeaterField(name: 'key', type: 'string', label: 'Key', required: true, width: '160px')]
    #[RepeaterField(name: 'label', type: 'string', label: 'Label', required: true)]
    #[RepeaterField(name: 'type', type: 'string', label: 'Type', required: true, width: '140px')]
    #[RepeaterField(name: 'required', type: 'boolean', label: 'Required', required: false, width: '90px')]
    #[RepeaterField(name: 'options', type: 'string', label: 'Options (one per line)', required: false)]
    public function fields(): HasMany
    {
        return $this->hasMany(FormField::class)->orderBy('order');
    }

    #[Field(type: 'relationManager', section: 'submissions_section', label: 'Submissions', required: false)]
    #[Relation(show: 'created_at', relatedModule: 'formSubmission')]
    public function submissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class);
    }

    protected $fillable = [
        'name', 'title', 'slug', 'success_message', 'notify_email', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
