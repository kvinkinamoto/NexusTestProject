<?php

namespace App\Nexus\Modules\ProductReview\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Nexus\Modules\ProductReview\Http\Resources\ReviewCommentResource;
use App\Nexus\Modules\ProductReview\Models\ReviewComment;
use App\Nexus\Modules\ProductReview\Requests\ReviewCommentStoreRequest;
use App\Nexus\Modules\ProductReview\Services\ReviewService;

class ReviewCommentController extends Controller
{
    public function store(ReviewCommentStoreRequest $request, ReviewService $service)
    {
        $comment = $service->addReviewComment($request->validated());

        return response()->json([
            'status' => 'success',
            'comment' => new ReviewCommentResource($comment),
        ]);
    }

    public function destroy(int $id)
    {
        ReviewComment::query()
            ->where('user_id', auth()->id())
            ->findOrFail($id)
            ->delete();

        return response()->json(['status' => 'success']);
    }
}
