<?php

namespace App\Nexus\Modules\WidgetPlacement\Requests;

use Nodex\Nexus\Http\Requests\NexusFormRequest;

class AdminStoreRequest extends NexusFormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'widget_key' => 'required|string',
            'is_enabled' => 'nullable|boolean',
            'config' => 'nullable',

            'relation' => 'nullable|array',
            'relation.assignments' => 'nullable|array',
            'relation.assignments.*.position' => 'required|string|max:255',
            'relation.assignments.*.target_template' => 'nullable|string|max:255',
            'relation.assignments.*.is_active' => 'nullable|boolean',
            'relation.assignments.*.sort_order' => 'nullable|integer',
        ];
    }
}
