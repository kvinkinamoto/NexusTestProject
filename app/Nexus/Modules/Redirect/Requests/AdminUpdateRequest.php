<?php

namespace App\Nexus\Modules\Redirect\Requests;

use App\Nexus\Modules\Redirect\Enums\RedirectStatusCode;
use Illuminate\Validation\Rule;
use Nodex\Nexus\Http\Requests\NexusFormRequest;

class AdminUpdateRequest extends NexusFormRequest
{
    public function rules(): array
    {
        return [
            'from_path' => ['required', 'string', 'max:255', Rule::unique('redirects', 'from_path')->ignore($this->id)],
            'to_path' => ['required', 'string', 'max:2048'],
            'status_code' => ['nullable', Rule::in(array_column(RedirectStatusCode::cases(), 'value'))],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    protected function beforeValidation(): void
    {
        if ($this->has('from_path')) {
            $this->merge(['from_path' => trim((string) $this->input('from_path'), '/')]);
        }

        if ($this->has('to_path')) {
            $this->merge(['to_path' => $this->normalizeToPath((string) $this->input('to_path'))]);
        }
    }

    private function normalizeToPath(string $path): string
    {
        $path = trim($path);

        if ($path === '' || str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
            return $path;
        }

        return '/'.$path;
    }
}
