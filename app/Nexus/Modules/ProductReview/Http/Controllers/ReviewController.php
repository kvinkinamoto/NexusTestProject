<?php

namespace App\Nexus\Modules\ProductReview\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Nexus\Modules\ProductReview\Http\Resources\ReviewResource;
use App\Nexus\Modules\ProductReview\Models\Review;
use App\Nexus\Modules\ProductReview\Requests\ReviewStoreRequest;
use App\Nexus\Modules\ProductReview\Requests\ToggleReactionRequest;
use App\Nexus\Modules\ProductReview\Services\ReviewService;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function __construct(
        private readonly ReviewService $service,
    ) {}

    public function index(Request $request)
    {
        $productId = (int) $request->input('id');
        $perPage = (int) ($request->input('perPage') ?: 10);
        $page = (int) ($request->input('page') ?: 1);

        return ReviewResource::collection($this->service->getReviewsById($productId, $perPage, $page, $request->input('sort')));
    }

    public function store(ReviewStoreRequest $request)
    {
        $this->service->addReview($request);

        return response()->json(['status' => 'success']);
    }

    public function destroy(int $id)
    {
        Review::query()
            ->where('user_id', auth()->id())
            ->findOrFail($id)
            ->delete();

        return response()->json(['status' => 'success']);
    }

    public function toggleReaction(ToggleReactionRequest $request)
    {
        $reactions = $this->service->toggleReaction($request->validated());

        return response()->json([
            'status' => 'success',
            'reactions' => $reactions,
        ]);
    }
}
