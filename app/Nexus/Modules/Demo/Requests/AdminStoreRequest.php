<?php

namespace App\Nexus\Modules\Demo\Requests;

use Nodex\Nexus\Http\Requests\NexusFormRequest;

/**
 * Once a module has a dedicated Request, StoreActionMethod/ModuleForm::rules()
 * use ONLY its rules() — NexusRuleCollector (which builds rules from
 * #[Field(rules:...)]) never runs for this module's store/update actions,
 * including for plain scalar fields, not just relations. See
 * NexusRuleCollector's own docblock: relation fields are deliberately
 * excluded from its output and "left to a dedicated Request's own
 * hand-written rules" — required here specifically for the 3 belongsToMany
 * + 1 belongsTo relation fields (relation.* keys with no rule are silently
 * dropped by Validator::validate(), so the relation never gets synced).
 */
class AdminStoreRequest extends NexusFormRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'title_i18n' => 'nullable|array',
            'title_i18n.*' => 'nullable|string',
            'description' => 'nullable|string',
            'count' => 'nullable|integer|min:0',
            'email' => 'nullable|email|unique:demos,email',
            // NOTE: store-only unique check; AdminUpdateRequest re-declares this with ->ignore($this->id).
            'secret' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'event_date' => 'nullable|date',
            'event_at' => 'nullable|date',
            'image' => 'nullable|string',
            'video' => 'nullable|string',
            'images' => 'nullable|array',
            'images.*' => 'nullable|string',
            'videos' => 'nullable|array',
            'videos.*' => 'nullable|string',
            'status' => 'nullable|string',
            'priority' => 'nullable|string',
            'rating' => 'nullable|integer|between:1,5',
            'phone' => 'nullable|string',
            'show_extra' => 'nullable|boolean',
            'extra_field' => 'nullable|string',
            'notes_json' => 'nullable',
            'api_only_note' => 'nullable|string',
            'locked_field' => 'nullable|string',

            'relation' => 'nullable|array',
            'relation.permissionsPreload' => 'nullable|array',
            'relation.permissionsPreload.*' => 'nullable|integer',
            'relation.permissionsSearchOnly' => 'nullable|array',
            'relation.permissionsSearchOnly.*' => 'nullable|integer',
            'relation.permissionsLoadMode' => 'nullable|array',
            'relation.permissionsLoadMode.*' => 'nullable|integer',
            'relation.primaryPermission' => 'nullable|integer',
            'relation.items' => 'nullable|array',
            'relation.items.*.title' => 'required|string',
            'relation.items.*.qty' => 'nullable|integer',
        ];
    }
}
