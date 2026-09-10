<?php

namespace App\Nexus\Modules\SocialNetwork\Requests;

use App\Nexus\Modules\SocialNetwork\Enums\SocialPlatformEnum;
use Illuminate\Validation\Rule;
use Nodex\Nexus\Http\Requests\NexusFormRequest;

class AdminStoreRequest extends NexusFormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['nullable', 'array'],
            'title.*' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'array'],
            'caption.*' => ['nullable', 'string', 'max:255'],
            'url' => ['required', 'string', 'max:255'],
            'logo' => ['nullable', 'string'],
            'key' => ['nullable', 'string', 'max:255'],
            'platform' => ['nullable', Rule::enum(SocialPlatformEnum::class)],
            'sync_posts' => ['boolean'],
            'publish' => ['boolean'],

            'relation' => ['nullable', 'array'],
            'relation.parent' => ['nullable', 'integer', 'exists:social_networks,id'],
        ];
    }
}
