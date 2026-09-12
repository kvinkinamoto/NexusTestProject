<?php

namespace App\Nexus\Modules\ProductReview\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewCommentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'review_id' => $this->review_id,
            'user_id' => $this->user_id,
            'guest_name' => $this->guest_name,
            'guest_email' => $this->guest_email,
            'parent_id' => $this->parent_id,
            'comment' => $this->comment,
            'publish' => $this->publish,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
