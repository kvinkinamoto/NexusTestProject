<?php

use App\Models\User;
use App\Nexus\Modules\ProductReview\Models\Review;
use App\Nexus\Modules\ProductReview\Models\ReviewComment;
use App\Nexus\Modules\ProductReview\Models\ReviewReaction;
use App\Nexus\Modules\ProductReview\Services\ReviewService;
use App\Nexus\Modules\ShopProduct\Models\ShopProduct;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'ShopProduct'])->run();
    $this->artisan('nexus:module:install', ['name' => 'Bonus'])->run();
    $this->artisan('nexus:module:install', ['name' => 'OrderStatus'])->run();
    $this->artisan('nexus:module:install', ['name' => 'Order'])->run();
    $this->artisan('nexus:module:install', ['name' => 'ProductReview'])->run();

    $this->service = app(ReviewService::class);
    $this->product = ShopProduct::create(['name' => 'Shirt', 'slug' => 'shirt', 'price' => 100, 'publish' => true]);
    $this->user = User::factory()->create();
});

it('creates a review for the authenticated user via the storefront endpoint', function () {
    $this->actingAs($this->user)
        ->postJson('/api/reviews', [
            'product_id' => $this->product->id,
            'rating' => 5,
            'comment' => 'Excellent',
        ])
        ->assertOk()
        ->assertJson(['status' => 'success']);

    $review = Review::query()->firstOrFail();

    expect($review->product_id)->toBe($this->product->id);
    expect($review->user_id)->toBe($this->user->id);
    expect($review->rating)->toBe(5);
});

it('computes the average published rating for a product', function () {
    Review::create(['product_id' => $this->product->id, 'user_name' => 'A', 'rating' => 5, 'publish' => true]);
    Review::create(['product_id' => $this->product->id, 'user_name' => 'B', 'rating' => 3, 'publish' => true]);
    Review::create(['product_id' => $this->product->id, 'user_name' => 'C', 'rating' => 1, 'publish' => false]);

    expect($this->service->getAverageRating($this->product->id))->toBe('4.0');
});

it('adds a top-level comment that auto-publishes', function () {
    $review = Review::create(['product_id' => $this->product->id, 'user_name' => 'A', 'rating' => 5, 'publish' => true]);
    $this->actingAs($this->user);

    $comment = $this->service->addReviewComment([
        'review_id' => $review->id,
        'comment' => 'I agree!',
    ]);

    expect($comment->publish)->toBeTrue();
    expect($comment->user_id)->toBe($this->user->id);
});

it('rejects a comment whose parent belongs to a different review', function () {
    $review = Review::create(['product_id' => $this->product->id, 'user_name' => 'A', 'rating' => 5, 'publish' => true]);
    $otherReview = Review::create(['product_id' => $this->product->id, 'user_name' => 'B', 'rating' => 4, 'publish' => true]);
    $foreignComment = ReviewComment::create(['review_id' => $otherReview->id, 'comment' => 'Elsewhere', 'publish' => true]);

    $this->service->addReviewComment([
        'review_id' => $review->id,
        'parent_id' => $foreignComment->id,
        'comment' => 'Reply',
    ]);
})->throws(DomainException::class);

it('toggles a review reaction: create, flip, then remove on repeat', function () {
    $this->actingAs($this->user);
    $review = Review::create(['product_id' => $this->product->id, 'user_name' => 'A', 'rating' => 5, 'publish' => true]);

    $this->service->toggleReaction(['review_id' => $review->id, 'value' => 1]);
    expect(ReviewReaction::query()->where('review_id', $review->id)->first()->value)->toBe(1);

    $this->service->toggleReaction(['review_id' => $review->id, 'value' => -1]);
    expect(ReviewReaction::query()->where('review_id', $review->id)->first()->value)->toBe(-1);

    $this->service->toggleReaction(['review_id' => $review->id, 'value' => -1]);
    expect(ReviewReaction::query()->where('review_id', $review->id)->exists())->toBeFalse();
});
