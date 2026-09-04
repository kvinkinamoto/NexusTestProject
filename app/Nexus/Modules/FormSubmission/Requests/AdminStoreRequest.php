<?php

namespace App\Nexus\Modules\FormSubmission\Requests;

use Nodex\Nexus\Http\Requests\NexusFormRequest;

/**
 * Never actually submitted by the admin UI — every FormSubmission field is
 * disabled: true (see Models/FormSubmission.php). Kept minimal only because
 * #[Requests] requires a store class same as every other module.
 */
class AdminStoreRequest extends NexusFormRequest
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
