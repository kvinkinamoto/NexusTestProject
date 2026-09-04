<?php

namespace App\Nexus\Modules\Form\Requests;

use Nodex\Nexus\Http\Requests\NexusFormRequest;

class AdminStoreRequest extends NexusFormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|alpha_dash|unique:forms,slug',
            'success_message' => 'nullable|string',
            'notify_email' => 'nullable|email',
            'is_active' => 'nullable|boolean',

            'relation' => 'nullable|array',
            'relation.fields' => 'nullable|array',
            'relation.fields.*.key' => 'required|string|max:100',
            'relation.fields.*.label' => 'required|string|max:255',
            'relation.fields.*.type' => 'required|string|in:text,email,textarea,number,select,radio,checkbox,date',
            'relation.fields.*.required' => 'nullable|boolean',
            'relation.fields.*.options' => 'nullable|string',
        ];
    }
}
