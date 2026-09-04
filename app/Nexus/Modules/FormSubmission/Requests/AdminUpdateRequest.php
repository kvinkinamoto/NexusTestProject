<?php

namespace App\Nexus\Modules\FormSubmission\Requests;

use Nodex\Nexus\Http\Requests\NexusFormRequest;

class AdminUpdateRequest extends NexusFormRequest
{
    public function rules(): array
    {
        return [
            'form_id' => 'nullable|integer',
            'data' => 'nullable',
            'ip_address' => 'nullable|string',
        ];
    }
}
