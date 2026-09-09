<?php

namespace App\Nexus\Modules\Sitemap\Requests;

use App\Nexus\Modules\Sitemap\Enums\SitemapChangeFrequency;
use Illuminate\Validation\Rule;
use Nodex\Nexus\Http\Requests\NexusFormRequest;

class AdminUpdateRequest extends NexusFormRequest
{
    public function rules(): array
    {
        return [
            'url' => ['required', 'string', 'max:2048'],
            'priority' => ['nullable', 'numeric', 'between:0,1'],
            'changefreq' => ['nullable', Rule::in(array_column(SitemapChangeFrequency::cases(), 'value'))],
            'lastmod' => ['nullable', 'date'],
            'is_active' => ['nullable', 'boolean'],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }
}
