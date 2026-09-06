<?php

namespace App\Nexus\Modules\Modules\Requests;

use Nodex\Nexus\Http\Requests\NexusFormRequest;

/**
 * Never actually submitted — this module has no 'create' TableAction (see
 * Models/Module.php). Kept minimal only because #[Requests] requires a
 * store class same as every other module.
 */
class AdminStoreRequest extends NexusFormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'nullable|string',
        ];
    }
}
