<?php

use App\Nexus\Modules\SocialNetwork\Models\SocialPost;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'SocialNetwork'])->run();
});

it('lists visible posts, newest first, without authentication', function () {
    SocialPost::create(['platform' => 'telegram', 'external_id' => '1', 'text' => 'Old', 'is_visible' => true, 'published_at' => now()->subDay()]);
    SocialPost::create(['platform' => 'telegram', 'external_id' => '2', 'text' => 'New', 'is_visible' => true, 'published_at' => now()]);
    SocialPost::create(['platform' => 'telegram', 'external_id' => '3', 'text' => 'Hidden', 'is_visible' => false, 'published_at' => now()]);

    $response = $this->getJson('/api/social/posts')->assertOk();

    $texts = collect($response->json('data'))->pluck('text');
    expect($texts->all())->toBe(['New', 'Old']);
});

it('filters by platform', function () {
    SocialPost::create(['platform' => 'telegram', 'external_id' => '1', 'text' => 'TG', 'is_visible' => true, 'published_at' => now()]);
    SocialPost::create(['platform' => 'youtube', 'external_id' => '2', 'text' => 'YT', 'is_visible' => true, 'published_at' => now()]);

    $response = $this->getJson('/api/social/posts?platform=youtube')->assertOk();

    $texts = collect($response->json('data'))->pluck('text');
    expect($texts->all())->toBe(['YT']);
});
