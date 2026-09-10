<?php

namespace App\Nexus\Modules\ProductReview\Services;

use App\Nexus\Modules\Bonus\Enums\BonusTypeEnum;
use App\Nexus\Modules\Bonus\Services\BonusService;
use App\Nexus\Modules\BonusTransaction\Models\BonusTransaction;
use App\Nexus\Modules\Order\Models\OrderProduct;
use App\Nexus\Modules\ProductReview\Models\Review;
use App\Nexus\Modules\ProductReview\Models\ReviewComment;
use App\Nexus\Modules\ProductReview\Models\ReviewReaction;
use App\Nexus\Modules\ProductReview\Requests\ReviewStoreRequest;
use Illuminate\Support\Str;

class ReviewService
{
    public function __construct(private readonly BonusService $bonusService) {}

    public function getReviewsById(int $productId, int $perPage, int $page, ?string $sort = null)
    {
        $query = Review::query()
            ->with(['user', 'images'])
            ->where('reviews.product_id', $productId)
            ->withCount([
                'reactions as likes_count' => fn ($q) => $q->where('value', 1),
                'reactions as dislikes_count' => fn ($q) => $q->where('value', -1),
            ])
            ->publish();

        $query->orderBy('created_at', $sort === 'date' ? 'asc' : 'desc');

        $reviews = $query->paginate($perPage, ['*'], 'page', $page);

        $reviews->getCollection()->transform(function (Review $review) {
            $review->comments_tree = ReviewComment::query()
                ->with(['reactions', 'user'])
                ->publish()
                ->withCount([
                    'reactions as likes_count' => fn ($q) => $q->where('value', 1),
                    'reactions as dislikes_count' => fn ($q) => $q->where('value', -1),
                ])
                ->where('review_id', $review->id)
                ->defaultOrder()
                ->get()
                ->toTree();

            return $review;
        });

        if ($sort === 'rating') {
            $reviews->setCollection(
                $reviews->getCollection()->sortByDesc(fn (Review $review) => $review->rating)->values()
            );
        }

        return $reviews;
    }

    public function addReviewComment(array $data): ReviewComment
    {
        $review = Review::query()->findOrFail($data['review_id']);

        if (! empty($data['parent_id']) && ! ReviewComment::query()->where('review_id', $review->id)->whereKey($data['parent_id'])->exists()) {
            throw new \DomainException('The parent comment does not belong to this review.');
        }

        return ReviewComment::create([
            'review_id' => $review->id,
            'parent_id' => $data['parent_id'] ?? null,
            'comment' => $data['comment'],
            'publish' => true,

            'user_id' => auth()->id(),
            'guest_name' => auth()->check() ? null : $data['name'],
            'guest_email' => auth()->check() ? null : $data['email'],
        ]);
    }

    public function getAverageRating(int $productId): string
    {
        $avg = Review::query()
            ->where('product_id', $productId)
            ->publish()
            ->avg('rating');

        return number_format((float) $avg, 1, '.', '');
    }

    public function getUserReviews()
    {
        $reviews = Review::query()
            ->where('user_id', auth()->id())
            ->with(['user', 'images', 'product'])
            ->withCount([
                'reactions as likes_count' => fn ($q) => $q->where('value', 1),
                'reactions as dislikes_count' => fn ($q) => $q->where('value', -1),
            ])
            ->publish()
            ->orderBy('created_at', 'desc')
            ->get();

        $reviews->transform(function (Review $review) {
            $review->comments_tree = ReviewComment::query()
                ->with(['reactions', 'user'])
                ->publish()
                ->withCount([
                    'reactions as likes_count' => fn ($q) => $q->where('value', 1),
                    'reactions as dislikes_count' => fn ($q) => $q->where('value', -1),
                ])
                ->where('review_id', $review->id)
                ->defaultOrder()
                ->get()
                ->toTree();

            return $review;
        });

        return $reviews;
    }

    public function addReview(ReviewStoreRequest $request): Review
    {
        $data = $request->validated();

        $review = Review::create([
            'product_id' => $data['product_id'],
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
            'advantages' => $data['advantages'] ?? null,
            'disadvantages' => $data['disadvantages'] ?? null,
            'video' => $data['video'] ?? null,
            'user_name' => $data['user_name'] ?? auth()->user()->name,
            'user_id' => auth()->id(),
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $filename = Str::uuid()->toString().'.'.$image->getClientOriginalExtension();
                $path = $image->storeAs('reviews/'.$review->id, $filename, 'public');

                $review->images()->create(['url' => $path]);
            }
        }

        return $review->load('images');
    }

    public function toggleReaction(array $data): array
    {
        $reviewId = $data['review_id'] ?? null;
        $commentId = $data['review_comment_id'] ?? null;
        $newValue = (int) $data['value'];

        if ($reviewId) {
            $review = Review::query()->findOrFail($reviewId);
        } else {
            $comment = ReviewComment::query()->findOrFail($commentId);
            $review = Review::query()->findOrFail($comment->review_id);
        }

        if ($commentId && ! ReviewComment::query()->where('review_id', $review->id)->whereKey($commentId)->exists()) {
            throw new \DomainException('The comment does not belong to this review.');
        }

        $query = ReviewReaction::query()
            ->where('review_id', $reviewId)
            ->where('review_comment_id', $commentId);

        if (auth()->check()) {
            $query->where('user_id', auth()->id());
        } else {
            $query->where('session_id', session()->getId());
        }

        $reaction = $query->first();

        if ($reaction) {
            $isSameReaction = ($newValue === 1 && $reaction->isLike()) || ($newValue === -1 && $reaction->isDislike());

            if ($isSameReaction) {
                $reaction->delete();
            } else {
                $reaction->update(['value' => $newValue]);
            }
        } else {
            ReviewReaction::create([
                'user_id' => auth()->id(),
                'session_id' => auth()->check() ? null : session()->getId(),
                'review_id' => $reviewId,
                'review_comment_id' => $commentId,
                'value' => $newValue,
            ]);
        }

        $counts = ReviewReaction::query()
            ->where('review_id', $reviewId)
            ->where('review_comment_id', $commentId)
            ->selectRaw('COUNT(CASE WHEN value = 1 THEN 1 END) as likes')
            ->selectRaw('COUNT(CASE WHEN value = -1 THEN 1 END) as dislikes')
            ->first();

        return [
            'likes_count' => $counts->likes,
            'dislikes_count' => $counts->dislikes,
        ];
    }

    public function getLastReviews()
    {
        return Review::query()
            ->with('product')
            ->whereHas('product')
            ->publish()
            ->join('shop_products', function ($join) {
                $join->on('reviews.product_id', '=', 'shop_products.id')
                    ->where('shop_products.publish', 1);
            })
            ->select('reviews.*')
            ->orderBy('reviews.created_at', 'desc')
            ->limit(16)
            ->get();
    }

    /**
     * Awards a REVIEW bonus only to a verified purchaser (a completed order containing this
     * product) and caps the number of REVIEW bonuses for a given user+product at the number
     * of distinct completed purchases of that product — so reviewing the same product twice
     * doesn't farm bonuses beyond how many times it was actually bought.
     */
    public function addBonusesForReview(Review $review): ?BonusTransaction
    {
        if (! $review->user_id || ! $review->publish) {
            return null;
        }

        $purchasesCount = OrderProduct::query()
            ->where('product_id', $review->product_id)
            ->whereHas('order', fn ($q) => $q->where('user_id', $review->user_id)->whereNotNull('completed_at'))
            ->count();

        if ($purchasesCount === 0) {
            return null;
        }

        $awardedCount = BonusTransaction::query()
            ->where('type', BonusTypeEnum::REVIEW)
            ->where('user_id', $review->user_id)
            ->where('source_type', (new Review)->getMorphClass())
            ->whereIn('source_id', Review::query()
                ->where('product_id', $review->product_id)
                ->where('user_id', $review->user_id)
                ->pluck('id'))
            ->count();

        if ($awardedCount >= $purchasesCount) {
            return null;
        }

        $alreadyForThisReview = BonusTransaction::query()
            ->where('type', BonusTypeEnum::REVIEW)
            ->where('source_type', (new Review)->getMorphClass())
            ->where('source_id', $review->id)
            ->exists();

        if ($alreadyForThisReview) {
            return null;
        }

        return $this->bonusService->awardReview($review->user, $review);
    }
}
