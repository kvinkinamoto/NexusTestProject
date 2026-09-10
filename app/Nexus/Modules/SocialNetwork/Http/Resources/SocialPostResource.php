<?php

namespace App\Nexus\Modules\SocialNetwork\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SocialPostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'platform' => $this->platform,
            'text' => $this->text,
            'media' => $this->media,
            'thumbnail' => $this->thumbnail,
            'has_video' => $this->hasVideo(),
            'url' => $this->post_url,
            'metrics' => $this->metrics,
            'published_at' => $this->published_at,
        ];
    }
}
