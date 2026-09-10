<?php

use App\Models\User;
use App\Nexus\Modules\ProductQuestion\Models\ProductQuestion;
use App\Nexus\Modules\ProductQuestion\Models\QuestionReaction;
use App\Nexus\Modules\ProductQuestion\Services\QuestionService;
use App\Nexus\Modules\ShopProduct\Models\ShopProduct;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'ShopProduct'])->run();
    $this->artisan('nexus:module:install', ['name' => 'ProductQuestion'])->run();

    $this->service = app(QuestionService::class);
    $this->product = ShopProduct::create(['name' => 'Shirt', 'slug' => 'shirt', 'price' => 100]);
});

it('creates an unpublished root question for a guest, pending moderation', function () {
    $question = $this->service->addQuestion([
        'product_id' => $this->product->id,
        'text' => 'Does this run small?',
        'name' => 'Guest Bob',
        'email' => 'guest@example.com',
    ]);

    expect($question->publish)->toBeFalse();
    expect($question->guest_name)->toBe('Guest Bob');
    expect($question->user_id)->toBeNull();
});

it('auto-publishes a reply', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $root = ProductQuestion::create(['product_id' => $this->product->id, 'text' => 'Root', 'publish' => true]);

    $reply = $this->service->addQuestion([
        'product_id' => $this->product->id,
        'parent_id' => $root->id,
        'text' => 'A reply',
    ]);

    expect($reply->publish)->toBeTrue();
    expect($reply->user_id)->toBe($user->id);
});

it('deletes only the authenticated user\'s own question', function () {
    $user = User::factory()->create();
    $question = ProductQuestion::create(['product_id' => $this->product->id, 'user_id' => $user->id, 'text' => 'Mine', 'publish' => true]);

    $this->actingAs($user);
    $this->service->deleteQuestion($question->id);

    expect(ProductQuestion::query()->find($question->id))->toBeNull();
});

it('toggles a reaction: create, flip, then remove on repeat', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $question = ProductQuestion::create(['product_id' => $this->product->id, 'text' => 'Q', 'publish' => true]);

    $this->service->toggleReaction(['question_id' => $question->id, 'value' => 1]);
    expect(QuestionReaction::query()->where('question_id', $question->id)->first()->value)->toBe(1);

    $this->service->toggleReaction(['question_id' => $question->id, 'value' => -1]);
    expect(QuestionReaction::query()->where('question_id', $question->id)->first()->value)->toBe(-1);

    $this->service->toggleReaction(['question_id' => $question->id, 'value' => -1]);
    expect(QuestionReaction::query()->where('question_id', $question->id)->exists())->toBeFalse();
});

it('builds a comments tree of published descendants for a root question', function () {
    $root = ProductQuestion::create(['product_id' => $this->product->id, 'text' => 'Root', 'publish' => true]);
    ProductQuestion::create(['product_id' => $this->product->id, 'parent_id' => $root->id, 'text' => 'Reply 1', 'publish' => true]);
    ProductQuestion::create(['product_id' => $this->product->id, 'parent_id' => $root->id, 'text' => 'Hidden reply', 'publish' => false]);

    $questions = $this->service->getQuestions($this->product->id, 10, 1);

    expect($questions->total())->toBe(1);
    expect($questions->getCollection()->first()->comments_tree)->toHaveCount(1);
});
