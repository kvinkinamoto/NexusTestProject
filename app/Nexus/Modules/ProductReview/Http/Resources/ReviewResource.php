<?php

namespace App\Nexus\Modules\ProductReview\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user' => $this->user ? ['id' => $this->user->id, 'name' => $this->user->name] : null,
            'user_name' => $this->user_name,
            'product_id' => $this->product_id,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'advantages' => $this->advantages,
            'disadvantages' => $this->disadvantages,
            'video' => $this->video,
            'likes_count' => $this->likes_count,
            'dislikes_count' => $this->dislikes_count,
            'images' => ReviewImageResource::collection($this->whenLoaded('images')),
            'comments_tree' => ReviewCommentNodeResource::collection($this->comments_tree ?? []),
            'created_at' => $this->created_at,
        ];
    }
}
