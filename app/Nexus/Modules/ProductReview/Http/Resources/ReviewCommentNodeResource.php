<?php

namespace App\Nexus\Modules\ProductReview\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A node of ReviewService::getReviewsById()'s comments_tree (built via
 * NodeTrait::toTree()) — distinct from ReviewCommentResource (the flat
 * single-comment shape returned by ReviewCommentController::store()) because
 * a tree node also carries its own likes_count/dislikes_count and a
 * recursive children collection.
 */
class ReviewCommentNodeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user' => $this->user ? ['id' => $this->user->id, 'name' => $this->user->name] : null,
            'guest_name' => $this->guest_name,
            'comment' => $this->comment,
            'likes_count' => $this->likes_count,
            'dislikes_count' => $this->dislikes_count,
            'created_at' => $this->created_at,
            'children' => self::collection($this->children),
        ];
    }
}
