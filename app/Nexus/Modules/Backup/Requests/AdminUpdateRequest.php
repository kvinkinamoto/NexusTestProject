<?php

namespace App\Nexus\Modules\Backup\Requests;

use Nodex\Nexus\Http\Requests\NexusFormRequest;

class AdminUpdateRequest extends NexusFormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'backup_database' => $this->boolean('backup_database'),
            'backup_code' => $this->boolean('backup_code'),
            'backup_storage' => $this->boolean('backup_storage'),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'backup_database' => 'boolean',
            'backup_code' => 'boolean',
            'backup_storage' => 'boolean',
            'backup_cron_expression' => 'nullable|string|max:255',
            'max_backups' => 'nullable|integer|min:1',
        ];
    }
}
