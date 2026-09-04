<?php

namespace App\Nexus\Modules\ConfirmFixture\Requests;

use Nodex\Nexus\Http\Requests\NexusFormRequest;

class AdminUpdateRequest extends NexusFormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string',
        ];
    }
}
