<?php

namespace App\Nexus\Modules\ProductReview\Observers;

use App\Nexus\Modules\ProductReview\Models\Review;
use App\Nexus\Modules\ProductReview\Services\ReviewService;

class ReviewObserver
{
    public function __construct(private readonly ReviewService $reviewService) {}

    public function created(Review $review): void
    {
        $this->reviewService->addBonusesForReview($review);
    }

    public function updated(Review $review): void
    {
        $this->reviewService->addBonusesForReview($review);
    }
}
