<?php

namespace App\Nexus\Modules\Demo\Requests;

use Illuminate\Validation\Rule;
use Nodex\Nexus\Http\Requests\NexusFormRequest;

/**
 * See AdminStoreRequest's docblock — same "dedicated Request owns every
 * rule, scalar and relation alike" requirement applies to update.
 */
class AdminUpdateRequest extends NexusFormRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'title_i18n' => 'nullable|array',
            'title_i18n.*' => 'nullable|string',
            'description' => 'nullable|string',
            'count' => 'nullable|integer|min:0',
            'email' => ['nullable', 'email', Rule::unique('demos', 'email')->ignore($this->id)],
            'secret' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'event_date' => 'nullable|date',
            'event_at' => 'nullable|date',
            'image' => 'nullable|string',
            'video' => 'nullable|string',
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
