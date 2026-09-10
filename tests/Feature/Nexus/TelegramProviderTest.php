<?php

use App\Nexus\Modules\SocialNetwork\Services\Providers\TelegramProvider;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config(['services.telegram.bot_token' => 'bot-token', 'services.telegram.channel_id' => '@mychannel']);

    $this->provider = app(TelegramProvider::class);
});

it('maps channel posts into DTOs and ignores non-post updates', function () {
    Http::fake([
        '*getUpdates*' => Http::response(['result' => [
            ['update_id' => 1, 'channel_post' => ['message_id' => 10, 'date' => 1700000000, 'text' => 'Hello world']],
            ['update_id' => 2, 'edited_message' => ['message_id' => 11]],
        ]], 200),
    ]);

    $posts = $this->provider->fetchPosts();

    expect($posts)->toHaveCount(1);
    expect($posts[0]->platform)->toBe('telegram');
    expect($posts[0]->externalId)->toBe('10');
    expect($posts[0]->text)->toBe('Hello world');
    expect($posts[0]->url)->toBe('https://t.me/mychannel/10');
});

it('resolves a photo attachment through getFile', function () {
    Http::fake([
        '*getUpdates*' => Http::response(['result' => [
            ['channel_post' => ['message_id' => 20, 'date' => 1700000000, 'photo' => [['file_id' => 'photo-1']]]],
        ]], 200),
        '*getFile*' => Http::response(['result' => ['file_path' => 'photos/file_1.jpg']], 200),
    ]);

    $posts = $this->provider->fetchPosts();

    expect($posts[0]->media[0]['url'])->toBe('https://api.telegram.org/file/botbot-token/photos/file_1.jpg');
});

it('returns an empty array when the request fails', function () {
    Http::fake(['*getUpdates*' => Http::response([], 500)]);

    expect($this->provider->fetchPosts())->toBe([]);
});
